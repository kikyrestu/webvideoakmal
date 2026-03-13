<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $siteName = setting('site_name', 'Video Portal');
        $siteDesc = setting('site_description', 'Portal video online');
        $siteUrl  = config('app.url');
        $siteLogo = setting('site_logo') ? Storage::url(setting('site_logo')) : '';
    @endphp

    <title>@yield('title', $siteName)@hasSection('title') — {{ $siteName }}@endif</title>
    <meta name="description" content="@yield('description', $siteDesc)">
    <link rel="canonical" href="{{ url()->current() }}">

    <meta property="og:site_name"   content="{{ $siteName }}">
    <meta property="og:title"       content="@yield('title', $siteName)@hasSection('title') — {{ $siteName }}@endif">
    <meta property="og:description" content="@yield('description', $siteDesc)">
    <meta property="og:url"         content="{{ url()->current() }}">
    <meta property="og:type"        content="@yield('og_type', 'website')">
    @hasSection('og_image')
        <meta property="og:image" content="@yield('og_image')">
    @elseif($siteLogo)
        <meta property="og:image" content="{{ $siteLogo }}">
    @endif

    <meta name="twitter:card"  content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', $siteName)@hasSection('title') — {{ $siteName }}@endif">
    <meta name="twitter:description" content="@yield('description', $siteDesc)">
    @hasSection('og_image')
        <meta name="twitter:image" content="@yield('og_image')">
    @elseif($siteLogo)
        <meta name="twitter:image" content="{{ $siteLogo }}">
    @endif

    @if(setting('site_favicon'))
        <link rel="icon" href="{{ Storage::url(setting('site_favicon')) }}">
    @endif

    <script type="application/ld+json">{"@context":"https://schema.org","@type":"WebSite","name":"{{ $siteName }}","url":"{{ $siteUrl }}","potentialAction":{"@type":"SearchAction","target":"{{ $siteUrl }}/search?q={search_term_string}","query-input":"required name=search_term_string"}}</script>

    @stack('head')

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
                <img src="{{ Storage::url($logoPath) }}" alt="{{ $siteName }}" style="height:34px;border-radius:6px;">
            @else
                <div class="logo-icon">
                    <svg fill="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px;">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </div>
            @endif
            <div>
                <div class="logo-text">{{ $siteName }}</div>
            </div>
        </a>

        <div class="header-search">
            <form action="/search" method="GET" style="display:flex;width:100%;">
                <input class="search-input" type="text" name="q"
                       placeholder="Cari video..." value="{{ request('q') }}"
                       autocomplete="off" aria-label="Cari video">
                <button type="submit" class="search-btn" aria-label="Cari">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</header>

{{-- ─── Navigation Tabs ───────────────────────────────────── --}}
<nav class="nav-tabs-bar" aria-label="Navigasi utama">
    <div class="nav-tabs-inner">
        @php $sidebarItems = json_decode(setting('sidebar_menu', '[]'), true) ?? []; @endphp
        @foreach($sidebarItems as $item)
            <a href="{{ $item['url'] ?? '#' }}"
               class="nav-tab {{ request()->is(ltrim($item['url'] ?? '', '/')) || (($item['url'] ?? '') === '/' && request()->is('/')) ? 'active' : '' }}">
                {{ $item['label'] ?? '' }}
            </a>
        @endforeach

        @if(count($sidebarItems))
        <div style="width:1px;height:20px;background:#2a2a2a;margin:0 8px;flex-shrink:0;align-self:center;"></div>
        @endif

        @foreach(\App\Models\Group::orderBy('sort_order')->get() as $group)
            <a href="/group/{{ $group->slug }}"
               class="nav-tab {{ request()->is('group/'.$group->slug) ? 'active' : '' }}">
                @if($group->logo_path)
                    <img src="{{ Storage::url($group->logo_path) }}"
                         style="width:16px;height:16px;border-radius:50%;object-fit:cover;"
                         alt="{{ $group->name }}">
                @endif
                {{ $group->name }}
            </a>
        @endforeach
    </div>
</nav>

{{-- ─── Main Body ─────────────────────────────────────────── --}}
<div class="body-wrapper">
    <main id="main-content">@yield('content')</main>

    {{-- ─── Right Sidebar ─────────────────────────────────── --}}
    <aside class="sidebar" aria-label="Sidebar">
        {{-- Video Terbaru --}}
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

        {{-- Populer --}}
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
                                    <img src="{{ Storage::url($pv->thumbnail_path) }}"
                                         alt="{{ $pv->title }}" loading="lazy">
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

        {{-- Kategori --}}
        <div class="box">
            <div class="box-header">
                <span class="box-title">Kategori</span>
            </div>
            <div style="padding:12px;display:flex;flex-wrap:wrap;gap:6px;">
                @php
                    $allCats = cache()->remember('sidebar_categories', 600, fn() =>
                        \App\Models\Category::withCount('videos')->get()
                    );
                @endphp
                @foreach($allCats as $cat)
                    <a href="/category/{{ $cat->slug }}" class="cat-chip"
                       style="--cat-color: {{ $cat->color }}">
                        <span class="cat-dot" style="background:{{ $cat->color }}"></span>
                        {{ $cat->name }}
                        <span class="cat-count">({{ $cat->videos_count }})</span>
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
