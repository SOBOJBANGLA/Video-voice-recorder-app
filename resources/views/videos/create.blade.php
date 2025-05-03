@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h2 class="h4 fw-bold mb-4">Record Screen & Webcam with Notes</h2>

    <!-- Preview -->
    <div class="position-relative border mb-3" style="height: 500px;">
        <video id="preview" autoplay muted playsinline class="w-100 h-100"></video>
        <div id="webcamPreview" class="position-absolute" style="bottom: 10px; right: 10px; width: 200px; height: 150px; border: 2px solid white; border-radius: 5px; overflow: hidden; background: #000; z-index: 1000;">
            <video id="webcamVideo" autoplay muted playsinline class="w-100 h-100"></video>
        </div>
    </div>

    <!-- Timer -->
    <div>
        <h5>Recording Timer: <span id="timer">00:00</span></h5>
    </div>

    <!-- Upload Form -->
    <form id="uploadForm" method="POST" action="{{ route('videos.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="title" id="titleInput">
        <input type="file" name="video" id="videoInput" hidden>
        <input type="hidden" name="notes" id="notesInput">
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>

    <!-- Controls -->
    <div class="mt-3">
        <button onclick="startRecording()" class="btn btn-success" id="startBtn">Start Recording</button>
        <button onclick="pauseRecording()" class="btn btn-warning" id="pauseBtn" disabled>Pause</button>
        <button onclick="resumeRecording()" class="btn btn-info" id="resumeBtn" disabled>Resume</button>
        <button onclick="stopRecording()" class="btn btn-danger" id="stopBtn" disabled>Stop</button>
    </div>

    <!-- Notes -->
    <div class="mt-4">
        <h5>Add Notes or Resources</h5>
        <div class="input-group mb-3">
            <input type="text" id="noteText" class="form-control" placeholder="Type note or resource URL">
            <button class="btn btn-info" onclick="addNote()">Add Note</button>
        </div>
        <div class="notes-container" style="max-height: 200px; overflow-y: auto;">
            <ul class="list-group" id="notesList"></ul>
        </div>
    </div>
</div>

<script>
let mediaRecorder;
let recordedBlobs = [];
let screenStream;
let webcamStream;
let microphoneStream;
let notes = [];
let startTime;
let timerInterval;
let canvas;
let ctx;
let isRecording = false;

// Start Timer function
function formatTime(seconds) {
    const mins = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
}

function startTimer() {
    startTime = Date.now();
    timerInterval = setInterval(() => {
        const elapsed = Math.floor((Date.now() - startTime) / 1000);
        document.getElementById('timer').innerText = formatTime(elapsed);
    }, 1000);
}

function stopTimer() {
    clearInterval(timerInterval);
    document.getElementById('timer').innerText = '00:00';
}

// Start recording function
async function startRecording() {
    try {
        // Get webcam stream first
        webcamStream = await navigator.mediaDevices.getUserMedia({
            video: {
                width: { ideal: 1280 },
                height: { ideal: 720 },
                frameRate: { ideal: 30 }
            },
            audio: false
        });

        // Set up webcam preview
        const webcamVideo = document.getElementById('webcamVideo');
        webcamVideo.srcObject = webcamStream;
        webcamVideo.autoplay = true;
        webcamVideo.playsInline = true;
        webcamVideo.style.objectFit = 'cover';
        await webcamVideo.play();

        // Get screen stream
        screenStream = await navigator.mediaDevices.getDisplayMedia({
            video: {
                width: { ideal: 1920 },
                height: { ideal: 1080 },
                frameRate: { ideal: 30 }
            },
            audio: false
        });

        // Handle screen sharing stop
        screenStream.getVideoTracks()[0].onended = () => {
            stopRecording();
        };

        // Create canvas for combining streams
        canvas = document.createElement('canvas');
        ctx = canvas.getContext('2d');
        canvas.width = screenStream.getVideoTracks()[0].getSettings().width || 1920;
        canvas.height = screenStream.getVideoTracks()[0].getSettings().height || 1080;

        // Set up screen preview
        const previewVideo = document.getElementById('preview');
        previewVideo.srcObject = screenStream;
        previewVideo.autoplay = true;
        previewVideo.playsInline = true;
        previewVideo.style.objectFit = 'contain';
        await previewVideo.play();

        // Get microphone stream
        microphoneStream = await navigator.mediaDevices.getUserMedia({
            audio: {
                echoCancellation: true,
                noiseSuppression: true,
                autoGainControl: true,
                channelCount: 2,
                sampleRate: 48000,
                sampleSize: 16
            }
        });

        // Create combined stream
        const combinedStream = new MediaStream();
        
        // Add screen video track
        screenStream.getVideoTracks().forEach(track => {
            combinedStream.addTrack(track);
        });

        // Add webcam video track
        webcamStream.getVideoTracks().forEach(track => {
            combinedStream.addTrack(track);
        });

        // Add audio track
        microphoneStream.getAudioTracks().forEach(track => {
            combinedStream.addTrack(track);
        });

        // Set up media recorder
        recordedBlobs = [];
        mediaRecorder = new MediaRecorder(combinedStream, {
            mimeType: 'video/webm;codecs=vp9,opus',
            videoBitsPerSecond: 8000000,
            audioBitsPerSecond: 128000
        });

        mediaRecorder.ondataavailable = (e) => {
            if (e.data && e.data.size > 0) {
                recordedBlobs.push(e.data);
            }
        };

        mediaRecorder.start(1000);
        startTimer();
        isRecording = true;

        // Enable controls
        document.getElementById('startBtn').disabled = true;
        document.getElementById('stopBtn').disabled = false;
        document.getElementById('pauseBtn').disabled = false;

        // Start drawing frames
        requestAnimationFrame(drawFrame);

    } catch (err) {
        console.error('Error starting recording:', err);
        alert('Please allow screen and camera access.');
    }
}

// Draw frame function
function drawFrame() {
    if (isRecording && screenStream && webcamStream) {
        const screenVideo = document.getElementById('preview');
        const webcamVideo = document.getElementById('webcamVideo');

        // Draw screen
        ctx.drawImage(screenVideo, 0, 0, canvas.width, canvas.height);

        // Draw webcam overlay
        const webcamWidth = 320; // Adjust size as needed
        const webcamHeight = 240;
        const x = canvas.width - webcamWidth - 20;
        const y = canvas.height - webcamHeight - 20;
        ctx.drawImage(webcamVideo, x, y, webcamWidth, webcamHeight);

        // Draw border around webcam
        ctx.strokeStyle = 'white';
        ctx.lineWidth = 2;
        ctx.strokeRect(x, y, webcamWidth, webcamHeight);

        requestAnimationFrame(drawFrame);
    }
}

// Pause recording
function pauseRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        mediaRecorder.pause();
        stopTimer();
        isRecording = false;
        document.getElementById('pauseBtn').disabled = true;
        document.getElementById('resumeBtn').disabled = false;
    }
}

// Resume recording
function resumeRecording() {
    if (mediaRecorder && mediaRecorder.state === 'paused') {
        mediaRecorder.resume();
        startTimer();
        isRecording = true;
        document.getElementById('pauseBtn').disabled = false;
        document.getElementById('resumeBtn').disabled = true;
        requestAnimationFrame(drawFrame);
    }
}

// Stop recording
function stopRecording() {
    if (mediaRecorder && mediaRecorder.state !== 'inactive') {
        mediaRecorder.stop();
        stopTimer();
        isRecording = false;

        if (screenStream) screenStream.getTracks().forEach(track => track.stop());
        if (webcamStream) webcamStream.getTracks().forEach(track => track.stop());
        if (microphoneStream) microphoneStream.getTracks().forEach(track => track.stop());

        document.getElementById('preview').srcObject = null;
        document.getElementById('webcamVideo').srcObject = null;

        const blob = new Blob(recordedBlobs, { type: 'video/webm' });
        const file = new File([blob], 'recording.webm', { type: 'video/webm' });
        
        const dt = new DataTransfer();
        dt.items.add(file);
        document.getElementById('videoInput').files = dt.files;

        document.getElementById('notesInput').value = JSON.stringify(notes);

        const title = prompt("Enter video title:");
        if (title) {
            document.getElementById('titleInput').value = title;
            document.getElementById('uploadForm').submit();
        }

        // Reset UI state
        document.getElementById('startBtn').disabled = false;
        document.getElementById('pauseBtn').disabled = true;
        document.getElementById('resumeBtn').disabled = true;
        document.getElementById('stopBtn').disabled = true;
    }
}

// Add note at current time
function addNote() {
    const noteText = document.getElementById('noteText').value.trim();
    if (noteText) {
        const currentTime = Math.floor((Date.now() - startTime) / 1000);
        const note = {
            time: currentTime,
            text: noteText,
            timestamp: formatTime(currentTime)
        };
        notes.push(note);

        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML = `
            <span>[${note.timestamp}] ${note.text}</span>
            <button class="btn btn-sm btn-danger" onclick="removeNote(${notes.length - 1})">×</button>
        `;
        document.getElementById('notesList').appendChild(li);

        document.getElementById('noteText').value = '';
        document.getElementById('noteText').focus();
    }
}

// Remove note
function removeNote(index) {
    notes.splice(index, 1);
    document.getElementById('notesList').innerHTML = '';
    notes.forEach((note, i) => {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML = `
            <span>[${note.timestamp}] ${note.text}</span>
            <button class="btn btn-sm btn-danger" onclick="removeNote(${i})">×</button>
        `;
        document.getElementById('notesList').appendChild(li);
    });
}
</script>
@endsection
