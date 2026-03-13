{{-- Reusable video card component --}}
<a href="/video/{{ $video->slug }}" class="video-card">
    <div class="video-card-thumb">
        @if($video->thumbnail_path)
            <img src="{{ Storage::url($video->thumbnail_path) }}" alt="{{ $video->title }}" loading="lazy">
        @else
            <div class="no-thumb">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:40px;height:40px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
                </svg>
            </div>
        @endif

        @if($video->is_live)
            <span class="video-card-live-badge">● Live</span>
        @endif

        @if($video->duration)
            <span class="video-card-duration">{{ $video->getFormattedDuration() }}</span>
        @endif
    </div>

    <div class="video-card-info">
        <div class="video-card-avatar">
            @if($video->group && $video->group->logo_path)
                <img src="{{ Storage::url($video->group->logo_path) }}" alt="">
            @else
                {{ strtoupper(substr($video->group?->name ?? 'V', 0, 1)) }}
            @endif
        </div>
        <div class="video-card-meta">
            <p class="video-card-title">{{ $video->title }}</p>
            <p class="video-card-sub">{{ $video->group?->name ?? '' }}</p>
            <p class="video-card-sub">{{ number_format($video->views_count) }} views · {{ $video->published_at?->diffForHumans() ?? '' }}</p>
            @if($video->category)
                <span class="video-card-category" style="background: {{ $video->category->color }}">
                    {{ $video->category->name }}
                </span>
            @endif
        </div>
    </div>
</a>
