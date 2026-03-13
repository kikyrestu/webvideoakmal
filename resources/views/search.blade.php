@extends('layouts.app')
@section('title', 'Hasil Cari: ' . $q . ' — ' . setting('site_name', 'Video Portal'))
@section('content')

<div class="search-header">
    <h1>Hasil pencarian untuk: <span>"{{ $q }}"</span></h1>
    @if($q && $videos instanceof \Illuminate\Contracts\Pagination\Paginator)
        <p style="color:#aaa;font-size:14px;margin-top:4px;">{{ $videos->total() }} hasil ditemukan</p>
    @endif
</div>

@if(strlen($q) < 2)
    <div class="empty-state">
        <h3>Masukkan minimal 2 karakter untuk mencari</h3>
    </div>
@elseif($videos instanceof \Illuminate\Contracts\Pagination\Paginator && $videos->count())
    <div class="video-grid">
        @foreach($videos as $video)
            @include('partials.video-card', ['video' => $video])
        @endforeach
    </div>
    <div class="pagination">{{ $videos->links() }}</div>
@else
    <div class="empty-state">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
        <h3>Tidak ada hasil untuk "{{ $q }}"</h3>
        <p>Coba kata kunci lain.</p>
    </div>
@endif

@endsection
