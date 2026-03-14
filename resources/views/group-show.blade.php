@extends('layouts.app')
@section('title', $group->name . ' — ' . setting('site_name', 'Video Portal'))

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "CollectionPage",
    "name": "{{ addslashes($group->name) }}",
    "url": "{{ url('/group/' . $group->slug) }}",
    @if($group->logo_path)
    "image": "{{ Storage::url($group->logo_path) }}",
    @endif
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
        {"@@type": "ListItem", "position": 2, "name": "{{ addslashes($group->name) }}"}
    ]
}
</script>
@endpush

@section('content')

<div class="page-header">
    <div class="page-header-avatar">
        @if($group->logo_path)<img src="{{ Storage::url($group->logo_path) }}" alt="{{ $group->name }}">
        @else {{ strtoupper(substr($group->name, 0, 1)) }} @endif
    </div>
    <div>
        <h1>{{ $group->name }}</h1>
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
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"/></svg>
        <h3>Belum ada video di group ini</h3>
    </div>
@endif

@endsection
