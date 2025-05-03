@extends('layouts.app')

@section('content')
    <div class="container mt-4">
        <h2 class="h4 fw-bold mb-3">{{ $video->title }}</h2>
        
        <div class="ratio ratio-16x9 mb-4">
            <video controls>
                <source src="{{ asset('storage/' . $video->file_path) }}" type="video/webm">
                Your browser does not support the video tag.
            </video>
        </div>
    </div>
@endsection
