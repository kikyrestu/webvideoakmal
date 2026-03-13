@extends('layouts.app')

@section('title', setting('site_name', 'Video Portal'))

@section('content')

{{-- Hashtag Bar --}}
<div class="hashtag-bar">
    <a href="/" class="hashtag-chip {{ !request('tag') ? 'active' : '' }}">Semua</a>
    @foreach($tags as $tag)
        <a href="/tag/{{ $tag->slug }}"
           class="hashtag-chip {{ request()->is('tag/'.$tag->slug) ? 'active' : '' }}">
            #{{ $tag->name }}
        </a>
    @endforeach
</div>

{{-- Video Sections per Group --}}
@forelse($groupsQuery as $group)
    <div class="section-header">
        <div class="section-title">
            @if($group->logo_path)
                <img src="{{ Storage::url($group->logo_path) }}" alt="{{ $group->name }}">
            @endif
            {{ $group->name }}
        </div>
        <a href="/group/{{ $group->slug }}" class="section-see-all">Lihat semua →</a>
    </div>

    <div class="video-grid">
        @foreach($group->videos as $video)
            @include('partials.video-card', ['video' => $video])
        @endforeach
    </div>
@empty
    <div class="empty-state">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/></svg>
        <h3>Belum ada video</h3>
        <p>Video akan muncul di sini setelah dipublish oleh admin.</p>
    </div>
@endforelse

@endsection
