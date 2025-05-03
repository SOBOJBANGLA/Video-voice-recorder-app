<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::latest()->paginate(8);
        return view('videos.index', compact('videos'));
    }

    public function show(Video $video)
    {
        return view('videos.show', compact('video'));
    }

    public function create()
    {
        return view('videos.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'title' => 'required|string|max:255',
                'video' => 'required|mimes:webm,mp4,mov|max:200000',
                'notes' => 'nullable|string'
            ]);

            $videoFile = $request->file('video');
            $path = $videoFile->store('videos', 'public');

            $notes = $request->input('notes');
            $notesJson = $notes ? json_encode(json_decode($notes), JSON_UNESCAPED_UNICODE) : null;

            Video::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'file_path' => $path,
                'notes' => $notesJson,
            ]);

            return redirect()->route('home')->with('success', 'Video uploaded successfully!');
        } catch (\Exception $e) {
            Log::error('Video upload error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while uploading the video.');
        }
    }
}
