<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', setting('site_name', 'Video Portal'))</title>
    <meta name="description" content="@yield('description', setting('site_description', 'Portal Video'))">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
</head>
<body>

{{-- ─── Header ────────────────────────────────────────────── --}}
<header class="site-header">
    <div class="header-inner">
        <a href="/" class="site-logo">
            @php $logoPath = setting('site_logo') @endphp
            @if($logoPath)
                <img src="{{ Storage::url($logoPath) }}" alt="logo" style="height:34px;border-radius:6px;">
            @else
                <div class="logo-icon">▶</div>
            @endif
            <div>
                <div class="logo-text">{{ setting('site_name', 'Video Portal') }}</div>
            </div>
        </a>

        <div class="header-search">
            <form action="/search" method="GET" style="display:flex;width:100%;">
                <input class="search-input" type="text" name="q"
                       placeholder="Cari video..." value="{{ request('q') }}" autocomplete="off">
                <button type="submit" class="search-btn">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</header>

{{-- ─── Navigation Tabs ───────────────────────────────────── --}}
<nav class="nav-tabs-bar">
    <div class="nav-tabs-inner">
        <a href="/" class="nav-tab home-tab {{ request()->is('/') && !request('filter') ? 'active' : '' }}">
            🏠 Home
        </a>

        @php $sidebarItems = json_decode(setting('sidebar_menu', '[]'), true) ?? []; @endphp
        @foreach($sidebarItems as $item)
            <a href="{{ $item['url'] ?? '#' }}"
               class="nav-tab {{ request()->is(ltrim($item['url'] ?? '', '/')) ? 'active' : '' }}">
                {{ $item['label'] ?? '' }}
            </a>
        @endforeach

        <div style="width:1px;height:20px;background:#2a2a2a;margin:0 8px;flex-shrink:0;align-self:center;"></div>

        @foreach(\App\Models\Group::orderBy('sort_order')->get() as $group)
            <a href="/group/{{ $group->slug }}"
               class="nav-tab {{ request()->is('group/'.$group->slug) ? 'active' : '' }}">
                @if($group->logo_path)
                    <img src="{{ Storage::url($group->logo_path) }}" style="width:16px;height:16px;border-radius:50%;object-fit:cover;">
                @endif
                {{ $group->name }}
            </a>
        @endforeach
    </div>
</nav>

{{-- ─── Main Body ─────────────────────────────────────────── --}}
<div class="body-wrapper">
    <main>@yield('content')</main>

    {{-- ─── Right Sidebar ─────────────────────────────────── --}}
    <aside class="sidebar">
        {{-- Latest Videos List --}}
        <div class="box">
            <div class="box-header">
                <span class="box-title">Video Terbaru</span>
            </div>
            <ul class="latest-list">
                @php
                    $recentVideos = cache()->remember('sidebar_recent', 300, fn() =>
                        \App\Models\Video::with('group')
                            ->where('status', 'published')
                            ->latest('published_at')
                            ->take(10)
                            ->get()
                    );
                @endphp
                @foreach($recentVideos as $i => $rv)
                    <li>
                        <a href="/video/{{ $rv->slug }}" class="latest-item">
                            <span class="latest-num">{{ $i + 1 }}</span>
                            <span class="latest-title">{{ $rv->title }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Popular Videos Thumbnails --}}
        <div class="box">
            <div class="box-header">
                <span class="box-title">Populer</span>
            </div>
            <ul class="thumb-list">
                @php
                    $popularVideos = cache()->remember('sidebar_popular', 300, fn() =>
                        \App\Models\Video::with(['group', 'category'])
                            ->where('status', 'published')
                            ->orderByDesc('views_count')
                            ->take(5)
                            ->get()
                    );
                @endphp
                @foreach($popularVideos as $pv)
                    <li>
                        <a href="/video/{{ $pv->slug }}" class="thumb-list-item">
                            <div class="thumb-list-img">
                                @if($pv->thumbnail_path)
                                    <img src="{{ Storage::url($pv->thumbnail_path) }}" alt="" loading="lazy">
                                @endif
                            </div>
                            <div class="thumb-list-info">
                                <div class="thumb-list-title">{{ $pv->title }}</div>
                                <div class="thumb-list-meta">{{ number_format($pv->views_count) }} views</div>
                            </div>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Categories --}}
        <div class="box">
            <div class="box-header">
                <span class="box-title">Kategori</span>
            </div>
            <div style="padding:12px;display:flex;flex-wrap:wrap;gap:6px;">
                @foreach(\App\Models\Category::withCount('videos')->get() as $cat)
                    <a href="/category/{{ $cat->slug }}"
                       style="display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:#222;border-radius:3px;font-size:12px;color:#ccc;transition:all .15s;"
                       onmouseover="this.style.background='{{ $cat->color }}';this.style.color='#fff'"
                       onmouseout="this.style.background='#222';this.style.color='#ccc'">
                        <span style="width:8px;height:8px;border-radius:50%;background:{{ $cat->color }};flex-shrink:0;"></span>
                        {{ $cat->name }}
                        <span style="color:#555">({{ $cat->videos_count }})</span>
                    </a>
                @endforeach
            </div>
        </div>
    </aside>
</div>

<script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>
@stack('scripts')
</body>
</html>
