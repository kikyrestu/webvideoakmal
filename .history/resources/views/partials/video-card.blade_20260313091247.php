{{-- Video Card untuk Grid --}}
<a href="/video/{{ $video->slug }}" class="video-card">
    <div class="video-thumb">
        @if($video->thumbnail_path)
            <img src="{{ Storage::url($video->thumbnail_path) }}" alt="{{ $video->title }}" loading="lazy">
        @else
            <div class="no-thumb">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
                </svg>
            </div>
        @endif

        <div class="thumb-overlay">
            <div class="play-btn-overlay">
                <svg fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </div>
        </div>

        @if($video->is_live)
            <span class="thumb-live">● LIVE</span>
        @endif

        @if($video->duration)
            <span class="thumb-duration">{{ $video->getFormattedDuration() }}</span>
        @endif

        @if($video->category)
            <span class="thumb-cat" style="background:{{ $video->category->color }}">
                {{ $video->category->name }}
            </span>
        @endif
    </div>

    <div class="video-card-info">
        <p class="video-card-title">{{ $video->title }}</p>
        <div class="video-card-meta">
            @if($video->group)
                <span>{{ $video->group->name }}</span>
                <span class="meta-dot"></span>
            @endif
            <span>{{ number_format($video->views_count) }} views</span>
            @if($video->published_at)
                <span class="meta-dot"></span>
                <span>{{ $video->published_at->diffForHumans() }}</span>
            @endif
        </div>
    </div>
</a>
