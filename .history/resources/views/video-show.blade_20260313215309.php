@extends('layouts.app')

@section('title', $video->title)
@section('description', Str::limit(strip_tags($video->description), 160))
@section('og_type', 'video.other')
@if($video->thumbnail_path)
@section('og_image', Storage::url($video->thumbnail_path))
@endif

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "VideoObject",
    "name": "{{ addslashes($video->title) }}",
    "description": "{{ addslashes(Str::limit(strip_tags($video->description ?? ''), 200)) }}",
    "thumbnailUrl": "{{ $video->thumbnail_path ? Storage::url($video->thumbnail_path) : '' }}",
    "uploadDate": "{{ $video->published_at?->toIso8601String() }}",
    "duration": "{{ $video->duration ? 'PT'.gmdate('H\Hi\Ms\S', $video->duration) : '' }}",
    "interactionStatistic": {
        "@@type": "InteractionCounter",
        "interactionType": "https://schema.org/WatchAction",
        "userInteractionCount": {{ $video->views_count }}
    }
}
</script>
@endpush

@section('content')

<div class="video-detail-layout">

{{-- ─── Main Column ─────────────────────────────────────────── --}}
<div>
    {{-- Player --}}
    <div class="video-player-wrap">
        @php $embedType = $video->getEmbedType(); @endphp

        @if($embedType === 'upload')
            <video id="player" controls crossorigin playsinline style="width:100%;height:100%;">
                <source src="{{ Storage::url($video->video_path) }}" type="video/mp4">
            </video>
        @elseif($embedType === 'youtube')
            <div id="player" data-plyr-provider="youtube" data-plyr-embed-id="{{ $video->embed_url }}" style="width:100%;height:100%;"></div>
        @elseif($embedType === 'vimeo')
            <div id="player" data-plyr-provider="vimeo" data-plyr-embed-id="{{ $video->embed_url }}" style="width:100%;height:100%;"></div>
        @else
            <iframe src="{{ $video->embed_url }}" style="width:100%;height:100%;border:none;" allowfullscreen allow="autoplay"></iframe>
        @endif
    </div>

    {{-- Video Info --}}
    <div class="video-info-section">
        <h1 class="video-title-main">{{ $video->title }}</h1>

        <div class="video-meta-row">
            <div class="video-meta-left">
                @if($video->group)
                    <a href="/group/{{ $video->group->slug }}" style="display:flex;align-items:center;gap:8px;text-decoration:none;color:#aaa;">
                        @if($video->group->logo_path)
                            <img src="{{ Storage::url($video->group->logo_path) }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover">
                        @endif
                        <span style="font-weight:600;color:#f1f1f1;">{{ $video->group->name }}</span>
                    </a>
                @endif
                <span>{{ number_format($video->views_count) }} views</span>
                <span>{{ $video->published_at?->format('d M Y') }}</span>
                @if($video->category)
                    <span class="video-card-category" style="background:{{ $video->category->color }}">{{ $video->category->name }}</span>
                @endif
            </div>

            <div class="video-actions" x-data="{
                liked: {{ $userHasLiked ? 'true' : 'false' }},
                likeCount: {{ $likesCount }},
                userRating: {{ $userRating ?? 0 }},
                avgRating: {{ $averageRating }},
                totalRatings: {{ $ratingsCount }},

                async toggleLike() {
                    const res = await fetch('/videos/{{ $video->id }}/like', {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content}
                    });
                    const data = await res.json();
                    this.liked = data.liked;
                    this.likeCount = data.count;
                },

                async submitRating(score) {
                    this.userRating = score;
                    const res = await fetch('/videos/{{ $video->id }}/rate', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({score})
                    });
                    const data = await res.json();
                    this.avgRating = data.average;
                    this.totalRatings = data.total;
                }
            }">
                {{-- Like Button --}}
                <button class="action-btn" :class="liked ? 'liked' : ''" @click="toggleLike">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558-.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 01-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.203 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 00-1.302 4.665c0 1.194.232 2.333.654 3.375z"/>
                    </svg>
                    <span x-text="likeCount"></span>
                </button>

                {{-- Share Button --}}
                <button class="action-btn" @click="navigator.clipboard.writeText(window.location.href); $el.textContent='✓ Copied!'">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z"/>
                    </svg>
                    Share
                </button>
            </div>
        </div>

        {{-- Rating Stars --}}
        <div x-data="{ userRating: {{ $userRating ?? 0 }}, hovered: 0 }" style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
            <div class="star-rating">
                @for($i = 1; $i <= 5; $i++)
                    <span class="star"
                          :class="(hovered >= {{ $i }} || userRating >= {{ $i }}) ? 'filled' : ''"
                          @mouseenter="hovered = {{ $i }}"
                          @mouseleave="hovered = 0"
                          @click="userRating = {{ $i }}; $dispatch('rate', {score: {{ $i }}})">★</span>
                @endfor
            </div>
            <span style="font-size:13px;color:#aaa;">{{ $averageRating }} / 5 ({{ $ratingsCount }} rating)</span>
        </div>

        {{-- Description --}}
        @if($video->description)
            <div class="description-box" x-data="{ expanded: false }">
                <div class="description-text" :class="expanded ? 'expanded' : ''">{{ $video->description }}</div>
                <button class="description-toggle" @click="expanded = !expanded" x-text="expanded ? 'Tampilkan lebih sedikit' : 'Tampilkan lebih banyak'"></button>
            </div>
        @endif

        {{-- Tags --}}
        @if($video->tags->count())
            <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:20px;">
                @foreach($video->tags as $tag)
                    <a href="/tag/{{ $tag->slug }}" class="hashtag-chip" style="font-size:12px;">#{{ $tag->name }}</a>
                @endforeach
            </div>
        @endif

        {{-- Comments Section --}}
        <div style="border-top:1px solid #2e2e2e;padding-top:20px;" x-data="{
            username: '',
            content: '',
            loading: false,
            success: false,
            async submit() {
                this.loading = true;
                const res = await fetch('/videos/{{ $video->id }}/comments', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({username: this.username, content: this.content})
                });
                this.loading = false;
                if (res.ok) {
                    this.success = true;
                    this.username = '';
                    this.content = '';
                }
            }
        }">
            <h3 style="font-size:16px;font-weight:700;margin-bottom:16px;">{{ $comments->count() }} Komentar</h3>

            <div class="comment-form">
                <input type="text" x-model="username" placeholder="Nama kamu..." maxlength="50">
                <textarea x-model="content" placeholder="Tulis komentar..." rows="3" maxlength="1000"></textarea>
                <div x-show="success" style="color:#4ade80;font-size:13px;margin-bottom:8px;">✓ Komentar terkirim! Menunggu moderasi admin.</div>
                <button class="btn-primary" @click="submit" :disabled="loading">
                    <span x-text="loading ? 'Mengirim...' : 'Kirim Komentar'"></span>
                </button>
            </div>

            @foreach($comments as $comment)
                <div class="comment-item">
                    <div class="comment-avatar">{{ strtoupper(substr($comment->username, 0, 1)) }}</div>
                    <div>
                        <div class="comment-username">
                            {{ $comment->username }}
                            @if($comment->is_from_admin)<span class="admin-badge">Admin</span>@endif
                        </div>
                        <div class="comment-content">{{ $comment->content }}</div>
                        <div class="comment-time">{{ $comment->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ─── Sidebar Related ─────────────────────────────────────── --}}
<div>
    <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;color:#f1f1f1;">Video Terkait</h3>
    @forelse($related as $rel)
        <a href="/video/{{ $rel->slug }}" class="related-video-item">
            <div class="related-thumb">
                @if($rel->thumbnail_path)
                    <img src="{{ Storage::url($rel->thumbnail_path) }}" alt="{{ $rel->title }}" loading="lazy">
                @else
                    <div style="width:100%;height:100%;background:#212121;display:flex;align-items:center;justify-content:center;color:#3a3a3a;">▶</div>
                @endif
            </div>
            <div>
                <div class="related-title">{{ $rel->title }}</div>
                <div class="related-meta">{{ $rel->group?->name }}</div>
                <div class="related-meta">{{ number_format($rel->views_count) }} views</div>
            </div>
        </a>
    @empty
        <p style="color:#555;font-size:13px;">Tidak ada video terkait.</p>
    @endforelse
</div>

</div>

@push('scripts')
<script>
    // Init Plyr
    const playerEl = document.getElementById('player');
    if (playerEl) {
        new Plyr('#player', {
            controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'],
        });
    }
</script>
@endpush

@endsection
