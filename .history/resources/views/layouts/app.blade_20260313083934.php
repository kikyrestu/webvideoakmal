<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', setting('site_name', 'Video Portal'))</title>
    <meta name="description" content="@yield('description', setting('site_description', 'Portal Video'))">
    <link rel="icon" href="{{ setting('site_favicon') ? Storage::url(setting('site_favicon')) : '/favicon.ico' }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
</head>
<body>

{{-- ─── Navbar ─────────────────────────────────────────────── --}}
<nav class="navbar">
    <a href="/" class="navbar-logo">
        @php $logoPath = setting('site_logo') @endphp
        @if($logoPath)
            <img src="{{ Storage::url($logoPath) }}" alt="logo" style="height:32px;border-radius:4px;">
        @else
            <div class="logo-icon">▶</div>
        @endif
        <span>{{ setting('site_name', 'Video Portal') }}</span>
    </a>

    <div class="navbar-search">
        <form action="/search" method="GET" class="search-form">
            <input type="text" name="q" placeholder="Cari video..." value="{{ request('q') }}" autocomplete="off">
            <button type="submit">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:18px;height:18px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
                </svg>
            </button>
        </form>
    </div>

    @php
        $navLabels = json_decode(setting('nav_filter_labels', '["Info","Umum"]'), true);
    @endphp
    <div class="navbar-filter">
        <button class="filter-btn {{ !request('filter') || request('filter') === 'all' ? 'active' : '' }}" onclick="location.href='/'">Semua</button>
        @foreach($navLabels ?? [] as $label)
            <button class="filter-btn {{ request('filter') === strtolower($label) ? 'active' : '' }}"
                    onclick="location.href='/?filter={{ strtolower($label) }}'">{{ $label }}</button>
        @endforeach
    </div>
</nav>

{{-- ─── Sidebar ─────────────────────────────────────────────── --}}
<aside class="sidebar">
    <nav class="sidebar-nav">
        @php
            $sidebarItems = json_decode(setting('sidebar_menu', '[]'), true) ?? [];
        @endphp

        @foreach($sidebarItems as $item)
            <a href="{{ $item['url'] ?? '#' }}" class="sidebar-link {{ request()->is(ltrim($item['url'] ?? '', '/')) ? 'active' : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>
                </svg>
                {{ $item['label'] ?? '' }}
            </a>
        @endforeach

        <div class="sidebar-divider"></div>

        <p class="sidebar-section">Jelajahi</p>

        @foreach(\App\Models\Group::orderBy('sort_order')->take(8)->get() as $group)
            <a href="/group/{{ $group->slug }}" class="sidebar-link">
                @if($group->logo_path)
                    <img src="{{ Storage::url($group->logo_path) }}" style="width:20px;height:20px;border-radius:50%;object-fit:cover">
                @else
                    <span style="width:20px;height:20px;border-radius:50%;background:#2e2e2e;display:inline-block;"></span>
                @endif
                {{ $group->name }}
            </a>
        @endforeach
    </nav>
</aside>

{{-- ─── Main Content ─────────────────────────────────────────── --}}
<main class="main-content">
    @yield('content')
</main>

</body>
</html>
