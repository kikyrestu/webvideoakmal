@extends('layouts.app')
@section('title', '#' . $tag->name . ' — ' . setting('site_name', 'Video Portal'))

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "CollectionPage",
    "name": "#{{ addslashes($tag->name) }}",
    "url": "{{ url('/tag/' . $tag->slug) }}",
    "mainEntity": {
        "@@type": "ItemList",
        "numberOfItems": {{ $videos->total() }},
        "itemListElement": [
            @foreach($videos->take(10) as $i => $v)
            {
                "@@type": "ListItem",
                "position": {{ $i + 1 }},
                "url": "{{ url('/video/' . $v->slug) }}",
                "name": "{{ addslashes($v->title) }}"
            }{{ !$loop->last ? ',' : '' }}
            @endforeach
        ]
    }
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        {"@@type": "ListItem", "position": 1, "name": "Home", "item": "{{ url('/') }}"},
        {"@@type": "ListItem", "position": 2, "name": "#{{ addslashes($tag->name) }}"}
    ]
}
</script>
@endpush

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
