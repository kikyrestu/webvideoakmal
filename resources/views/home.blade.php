@extends('layouts.app')

@section('title', setting('site_name', 'Video Portal') . ' — Portal Video Online')

@push('head')
@php
    $allHomeVideos = collect();
    foreach(($groupsQuery ?? collect()) as $g) {
        $allHomeVideos = $allHomeVideos->merge($g->videos);
    }
    $allHomeVideos = $allHomeVideos->merge($ungroupedVideos ?? collect())->take(20);
@endphp
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "ItemList",
    "name": "{{ addslashes(setting('site_name', 'Video Portal')) }}",
    "itemListElement": [
        @foreach($allHomeVideos as $i => $hv)
        {
            "@@type": "ListItem",
            "position": {{ $i + 1 }},
            "url": "{{ url('/video/' . $hv->slug) }}",
            "name": "{{ addslashes($hv->title) }}"
        }{{ !$loop->last ? ',' : '' }}
        @endforeach
    ]
}
</script>
@endpush

@section('content')

{{-- Group Filter Chips --}}
@php
    $groups = \App\Models\Group::orderBy('sort_order')->get();
    $tags   = cache()->remember('tags_sidebar', 3600, fn() =>
        \App\Models\Tag::withCount('videos')->orderByDesc('videos_count')->take(15)->get()
    );
@endphp

@if($groups->count())
<div class="box" style="margin-bottom:16px;">
    <div class="group-strip">
        <a href="/" class="group-chip {{ !request('g') ? 'active' : '' }}">Semua</a>
        @foreach($groups as $g)
            <a href="/?g={{ $g->slug }}" class="group-chip {{ request('g') === $g->slug ? 'active' : '' }}">
                @if($g->logo_path)
                    <img src="{{ Storage::url($g->logo_path) }}" alt="">
                @endif
                {{ $g->name }}
            </a>
        @endforeach
    </div>
</div>
@endif

{{-- Video sections per group --}}
@forelse($groupsQuery as $group)
    <div class="box">
        <div class="box-header">
            <span class="box-title">
                @if($group->logo_path)
                    <img src="{{ Storage::url($group->logo_path) }}"
                         style="width:20px;height:20px;border-radius:50%;object-fit:cover;display:inline-block;vertical-align:middle;">
                @endif
                {{ $group->name }}
            </span>
            <a href="/group/{{ $group->slug }}" class="box-more">
                Lihat semua
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                </svg>
            </a>
        </div>
        <div class="box-body">
            <div class="video-grid">
                @foreach($group->videos as $video)
                    @include('partials.video-card', ['video' => $video])
                @endforeach
            </div>
        </div>
    </div>
@empty
@endforelse

{{-- Section: Video tanpa group --}}
@if($ungroupedVideos->count())
    <div class="box">
        <div class="box-header">
            <span class="box-title">Lainnya</span>
        </div>
        <div class="box-body">
            <div class="video-grid">
                @foreach($ungroupedVideos as $video)
                    @include('partials.video-card', ['video' => $video])
                @endforeach
            </div>
        </div>
    </div>
@endif

{{-- Empty state jika benar-benar tidak ada video sama sekali --}}
@if($groupsQuery->isEmpty() && $ungroupedVideos->isEmpty())
    <div class="empty-state">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/>
        </svg>
        <h3>Belum ada video</h3>
        <p>Video akan muncul di sini setelah dipublikasikan admin.</p>
    </div>
@endif

@endsection
