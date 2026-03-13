@extends('layouts.app')
@section('title', '#' . $tag->name . ' — ' . setting('site_name', 'Video Portal'))
@section('content')

<div class="page-header">
    <div class="page-header-avatar" style="background:#2e2e2e;font-size:20px;">#</div>
    <div>
        <h1>#{{ $tag->name }}</h1>
        <p>{{ $videos->total() }} video</p>
    </div>
</div>

@if($videos->count())
    <div class="video-grid">
        @foreach($videos as $video)
            @include('partials.video-card', ['video' => $video])
        @endforeach
    </div>
    <div class="pagination">{{ $videos->links() }}</div>
@else
    <div class="empty-state">
        <h3>Belum ada video dengan tag ini</h3>
    </div>
@endif

@endsection
