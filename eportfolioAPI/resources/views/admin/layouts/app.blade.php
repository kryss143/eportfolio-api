<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') — {{ config('app.name', 'EPortfolio') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/css/admin.css', 'resources/js/app.js', 'resources/js/admin.js'])
    @endif
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:bg-indigo-600 focus:text-white focus:px-4 focus:py-2 focus:rounded-md">Skip to content</a>

    {{-- Alpine state: sidebarOpen (mobile), sidebarCollapsed (desktop) --}}
    <div class="h-full" x-data="{ sidebarOpen: false, isMd: window.matchMedia('(min-width: 768px)').matches, sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true', darkMode: localStorage.getItem('darkMode') === 'true', tip: '', tipY: 0, tipX: 0, tipBelow: false, tipTimer: null, showTip(el, label, pos = 'right') { if (pos === 'right' && (!this.sidebarCollapsed || !this.isMd)) return; const r = el.getBoundingClientRect(); clearTimeout(this.tipTimer); this.tipTimer = setTimeout(() => { if (pos === 'below') { this.tipBelow = true; this.tipX = r.left + r.width / 2; this.tipY = r.bottom + 8; } else { this.tipBelow = false; this.tipY = r.top + r.height / 2; } this.tip = label; }, 250); }, hideTip() { clearTimeout(this.tipTimer); this.tipTimer = null; this.tip = ''; } }" x-init="$watch('darkMode', v => { localStorage.setItem('darkMode', v); document.documentElement.classList.toggle('dark', v) }); $watch('sidebarCollapsed', v => { localStorage.setItem('sidebarCollapsed', v); clearTimeout(tipTimer); tipTimer = null; tip = '' }); document.documentElement.classList.toggle('dark', darkMode); const mq = window.matchMedia('(min-width: 768px)'); mq.addEventListener('change', e => { isMd = e.matches; if (!e.matches) { sidebarOpen = false; hideTip(); } })">

        {{-- Mobile overlay --}}
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/50 md:hidden" aria-hidden="true"></div>

        {{-- Collapsed-sidebar tooltip (fixed, outside the overflow-hidden sidebar) --}}
        <div x-show="tip" x-cloak x-transition.opacity.duration.100ms role="tooltip" x-text="tip"
            class="fixed z-[60] pointer-events-none px-2.5 py-1.5 rounded-md bg-gray-900 text-white text-xs font-medium whitespace-nowrap shadow-lg dark:bg-gray-700 dark:text-gray-100"
            :style="tipBelow ? `top: ${tipY}px; left: ${tipX}px; transform: translate(-50%, 0)` : `top: ${tipY}px; left: 4.5rem; transform: translate(0, -50%)`"></div>

        {{-- ═══ SIDEBAR (always fixed on desktop, slide on mobile) ═══ --}}
        <aside x-cloak :style="`width: ${!isMd || !sidebarCollapsed ? '15rem' : '4rem'}`" class="fixed inset-y-0 left-0 z-50 w-60 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col transition-all duration-200 overflow-hidden"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">

            {{-- Brand --}}
            <div class="flex items-center gap-2 px-4 h-14 border-b border-gray-200 dark:border-gray-700 shrink-0">
                <div class="w-7 h-7 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-sm shrink-0">EP</div>
                <span x-show="!sidebarCollapsed || !isMd" x-cloak class="font-semibold text-sm whitespace-nowrap">EPortfolio Admin</span>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3 px-2 space-y-1" aria-label="Main navigation">
                @php
                $navItems = [
                    ['route' => 'admin.dashboard', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />', 'label' => 'Overview', 'routeIs' => 'admin.dashboard'],
                ];
                $contentItems = [
                    ['route' => 'admin.projects.index', 'label' => 'Projects', 'routeIs' => 'admin.projects.*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />'],
                    ['route' => 'admin.blogs.index', 'label' => 'Blogs', 'routeIs' => 'admin.blogs.*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />'],
                    ['route' => 'admin.experiences.index', 'label' => 'Experience', 'routeIs' => 'admin.experiences.*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />'],
                    ['route' => 'admin.metrics.index', 'label' => 'Metrics', 'routeIs' => 'admin.metrics.*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />'],
                    ['route' => 'admin.skills.index', 'label' => 'Skills', 'routeIs' => 'admin.skills.*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.384 3.18A1.125 1.125 0 014.5 17.31V5.69c0-.4.213-.77.559-.97l5.384-3.18a1.125 1.125 0 011.122 0l5.384 3.18c.346.2.559.57.559.97v11.62c0 .4-.213.77-.559.97l-5.384 3.18a1.125 1.125 0 01-1.122 0z" />'],
                    ['route' => 'admin.tech-skills.index', 'label' => 'Tech Skills', 'routeIs' => 'admin.tech-skills.*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />'],
                ];
                $systemItems = [
                    ['route' => 'admin.users.index', 'label' => 'Users', 'routeIs' => 'admin.users.*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />'],
                    ['route' => 'admin.activity.index', 'label' => 'Activity', 'routeIs' => 'admin.activity.*', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />'],
                ];
                @endphp

                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" class="flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg {{ request()->routeIs($item['routeIs']) ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}" aria-label="{{ $item['label'] }}" {!! tooltip_attrs($item['label']) !!}>
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $item['icon'] !!}</svg>
                        <span x-show="!sidebarCollapsed || !isMd" x-cloak class="whitespace-nowrap">{{ $item['label'] }}</span>
                    </a>
                @endforeach

                <div x-show="!sidebarCollapsed || !isMd" x-cloak class="pt-3 pb-1 px-3 text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Content</div>

                @foreach ($contentItems as $item)
                    <a href="{{ route($item['route']) }}" class="flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg {{ request()->routeIs($item['routeIs']) ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}" aria-label="{{ $item['label'] }}" {!! tooltip_attrs($item['label']) !!}>
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $item['icon'] !!}</svg>
                        <span x-show="!sidebarCollapsed || !isMd" x-cloak class="whitespace-nowrap">{{ $item['label'] }}</span>
                    </a>
                @endforeach

                <div x-show="!sidebarCollapsed || !isMd" x-cloak class="pt-3 pb-1 px-3 text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">System</div>

                @foreach ($systemItems as $item)
                    <a href="{{ route($item['route']) }}" class="flex items-center gap-2.5 px-3 py-2 text-sm rounded-lg {{ request()->routeIs($item['routeIs']) ? 'bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-medium' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50' }}" aria-label="{{ $item['label'] }}" {!! tooltip_attrs($item['label']) !!}>
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $item['icon'] !!}</svg>
                        <span x-show="!sidebarCollapsed || !isMd" x-cloak class="whitespace-nowrap">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            {{-- Collapse toggle --}}
            <div class="hidden md:flex border-t border-gray-200 dark:border-gray-700 p-2 shrink-0">
                <button type="button" @click="sidebarCollapsed = !sidebarCollapsed" {!! tooltip_attrs_expr("sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'") !!} class="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700/50 rounded-lg transition-colors" :aria-label="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'" :aria-pressed="String(sidebarCollapsed)">
                    <svg class="w-4 h-4 transition-transform duration-200" :class="sidebarCollapsed && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" /></svg>
                    <span x-show="!sidebarCollapsed || !isMd" x-cloak class="whitespace-nowrap">Collapse</span>
                </button>
            </div>
        </aside>

        {{-- Main column (margin-left matches sidebar width) --}}
        <div x-cloak :style="`margin-left: ${sidebarCollapsed ? '4rem' : '15rem'}`" class="md:ml-60 hidden md:flex flex-col min-h-screen transition-all duration-200">

            {{-- Top bar --}}
            <header class="sticky top-0 z-30 h-14 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center gap-4 px-4 lg:px-6 shrink-0">
                <button @click="sidebarOpen = true" class="md:hidden p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" aria-label="Open menu">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>
                <div class="flex-1"></div>
                <button @click="darkMode = !darkMode; showTip($el, darkMode ? 'Switch to light mode' : 'Switch to dark mode', 'below')" {!! tooltip_attrs_expr("darkMode ? 'Switch to light mode' : 'Switch to dark mode'", 'below') !!} class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                    <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                </button>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open; hideTip()" {!! tooltip_attrs('Account menu', 'below') !!} class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                        <div class="w-7 h-7 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-xs font-semibold">{{ substr(auth()->user()->name ?? 'A', 0, 1) }}</div>
                        <span class="hidden sm:block text-sm font-medium">{{ auth()->user()->name ?? 'Admin' }}</span>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak x-transition class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg py-1 z-50">
                        <div class="px-3 py-2 text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email ?? '' }}</div>
                        <hr class="border-gray-200 dark:border-gray-700">
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Log out</button>
                        </form>
                    </div>
                </div>
            </header>

            {{-- Mock-data banner: MongoDB unreachable --}}
            @if (! $mongoAvailable)
                <div x-data="{ mockBannerHidden: sessionStorage.getItem('mockBannerHidden') === '1' }" x-show="!mockBannerHidden" x-cloak class="mx-4 lg:mx-6 mt-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-sm flex items-center gap-2" role="status">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    <span class="flex-1">MongoDB is unreachable — you're viewing <strong>sample data</strong>. Changes can't be saved right now.</span>
                    <button type="button" @click="mockBannerHidden = true; sessionStorage.setItem('mockBannerHidden', '1')" class="p-1 rounded hover:bg-amber-100 dark:hover:bg-amber-900/40 shrink-0" aria-label="Dismiss">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="mx-4 lg:mx-6 mt-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 text-sm flex items-center gap-2" role="status">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mx-4 lg:mx-6 mt-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 text-sm flex items-center gap-2" role="alert">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    {{ session('error') }}
                </div>
            @endif

            <main id="main" class="flex-1 p-4 lg:p-6">
                @yield('content')
            </main>
        </div>

        {{-- Mobile-only main content (below sidebar) --}}
        <div class="md:hidden flex flex-col min-h-screen" :style="`margin-left: 0`">
            <header class="sticky top-0 z-30 h-14 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center gap-4 px-4 shrink-0">
                <button @click="sidebarOpen = true" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700" aria-label="Open menu">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>
                <span class="font-semibold text-sm">Admin</span>
                <div class="flex-1"></div>
                <button @click="darkMode = !darkMode" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                    <svg x-show="darkMode" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                </button>
            </header>
            {{-- Mock-data banner: MongoDB unreachable --}}
            @if (! $mongoAvailable)
                <div x-data="{ mockBannerHidden: sessionStorage.getItem('mockBannerHidden') === '1' }" x-show="!mockBannerHidden" x-cloak class="mx-4 mt-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-200 text-sm flex items-center gap-2" role="status">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                    <span class="flex-1">MongoDB is unreachable — you're viewing <strong>sample data</strong>. Changes can't be saved right now.</span>
                    <button type="button" @click="mockBannerHidden = true; sessionStorage.setItem('mockBannerHidden', '1')" class="p-1 rounded hover:bg-amber-100 dark:hover:bg-amber-900/40 shrink-0" aria-label="Dismiss">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            @endif

            @if (session('success'))
                <div class="mx-4 mt-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 text-sm flex items-center gap-2" role="status">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="mx-4 mt-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 text-sm flex items-center gap-2" role="alert">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                    {{ session('error') }}
                </div>
            @endif
            <main id="main-mobile" class="flex-1 p-4">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
