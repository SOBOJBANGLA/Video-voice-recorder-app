<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Videos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    {{-- Header with Nav --}}
    <header class="bg-dark text-white p-3 mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">Video Library</h1>
            <nav>
                <ul class="nav">
                    <li class="nav-item"><a class="nav-link text-white" href="{{ route('home') }}">Home</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="{{ route('dashboard') }}">Upload Video</a></li>
                    
                    @auth
                        <li class="nav-item"><a class="nav-link text-white" href="#">{{ Auth::user()->name }}</a></li>
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST" class="nav-link">
                                @csrf
                                <button type="submit" class="nav-link btn btn-link text-white p-0 m-0">Logout</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link text-white" href="{{ route('register') }}">Register</a></li>
                        <li class="nav-item"><a class="nav-link text-white" href="{{ route('login') }}">Login</a></li>
                    @endauth
                </ul>
            </nav>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="container mb-5">
        <h2 class="h4 fw-bold mb-4">All Videos</h2>

        <div class="row">
            @foreach($videos as $video)
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <a href="{{ route('videos.show', $video) }}">
                            <video class="card-img-top" controls muted>
                                <source src="{{ asset('storage/' . $video->file_path) }}" type="video/webm">
                                Your browser does not support the video tag.
                            </video>
                        </a>
                        <div class="card-body d-flex flex-column">
                            <p class="card-title fw-semibold">{{ $video->title }}</p>

                            @if($video->notes)
                                <h6>Helpful Resources</h6>
                                <ul class="list-group small mb-2">
                                    @foreach(json_decode($video->notes, true) as $note)
                                        <li class="list-group-item py-1 px-2">
                                            <strong>[{{ gmdate('i:s', $note['time']) }}]</strong> {{ $note['text'] }}
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            <a href="{{ route('videos.show', $video) }}" class="btn btn-sm btn-primary mt-auto">View Full</a>

                            {{-- Share Buttons --}}
                            <div class="mt-3">
                                <span class="fw-bold">Share:</span>
                                @php
                                    $shareUrl = urlencode(route('videos.show', $video));
                                    $shareText = urlencode($video->title);
                                @endphp

                                <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary me-1">
                                    Facebook
                                </a>
                                <a href="https://twitter.com/intent/tweet?url={{ $shareUrl }}&text={{ $shareText }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-info me-1">
                                    Twitter
                                </a>
                                <a href="https://wa.me/?text={{ $shareText }}%20{{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success me-1">
                                    WhatsApp
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-secondary me-1">
                                    LinkedIn
                                </a>
                                <a href="https://youtube.com/share?url={{ $shareUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-danger me-1">
                                    YouTube
                                </a>
                                <button class="btn btn-sm btn-outline-dark" onclick="navigator.clipboard.writeText('{{ route('videos.show', $video) }}'); this.innerText='Copied!'">
                                    Copy Link
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $videos->links() }}
        </div>
    </main>

    {{-- Footer --}}
    <footer class="bg-light text-center py-3 border-top">
        <p class="mb-0">&copy; {{ date('Y') }} Video Library. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
