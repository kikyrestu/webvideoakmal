@extends('layouts.app')
@section('title', $category->name . ' — ' . setting('site_name', 'Video Portal'))
@section('content')

<div class="page-header">
    <div class="page-header-avatar" style="background: {{ $category->color }}">
        {{ strtoupper(substr($category->name, 0, 1)) }}
    </div>
    <div>
        <h1>{{ $category->name }}</h1>
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
        <h3>Belum ada video dengan kategori ini</h3>
    </div>
@endif

@endsection
