<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'Overview') — {{ config('app.name', 'EPortfolio') }} Admin</title>
    {{-- Apply the saved theme before first paint to avoid a flash of the wrong theme. --}}
    <script>
        if (localStorage.getItem('darkMode') === 'true' ||
            (localStorage.getItem('darkMode') === null && matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    @endif
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="h-full bg-gray-100 font-sans text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-[70] focus:rounded-lg focus:bg-indigo-600 focus:px-4 focus:py-2 focus:text-white">Skip to content</a>

    {{-- Root Alpine state: drawer (under lg) + dark mode. --}}
    <div x-data="{
            drawer: false,
            isLg: matchMedia('(min-width: 1024px)').matches,
            darkMode: document.documentElement.classList.contains('dark')
        }"
        x-init="matchMedia('(min-width: 1024px)').addEventListener('change', e => { isLg = e.matches; if (!e.matches) drawer = false }); $watch('darkMode', v => { localStorage.setItem('darkMode', v); document.documentElement.classList.toggle('dark', v) })"
        x-effect="document.body.classList.toggle('overflow-hidden', drawer && !isLg)"
        @keydown.escape.window="drawer = false"
        class="flex min-h-dvh">

        {{-- Drawer backdrop (under lg) --}}
        <div x-show="drawer" x-cloak @click="drawer = false" aria-hidden="true"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

        {{-- ═══ Sidebar: static column ≥ lg, off-canvas drawer below ═══ --}}
        <aside id="sidebar" aria-label="Main navigation"
            class="fixed inset-y-0 left-0 z-50 flex w-60 shrink-0 flex-col border-r border-gray-200 bg-white transition-transform duration-200 motion-reduce:transition-none dark:border-gray-800 dark:bg-gray-900 lg:sticky lg:top-0 lg:h-dvh lg:translate-x-0"
            :class="drawer ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            :inert="!isLg && !drawer">
            <div class="flex h-14 shrink-0 items-center gap-2.5 border-b border-gray-200 px-4 dark:border-gray-800">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                    <span class="grid size-7 shrink-0 place-items-center rounded-lg bg-indigo-600 text-sm font-bold text-white" aria-hidden="true">EP</span>
                    <span class="text-sm font-semibold whitespace-nowrap">EPortfolio Admin</span>
                </a>
                <button type="button" @click="drawer = false" aria-label="Close menu"
                    class="ml-auto grid size-8 place-items-center rounded-lg text-gray-500 hover:bg-gray-100 lg:hidden dark:hover:bg-gray-800">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <nav class="flex-1 space-y-1 overflow-y-auto px-2 py-3 text-sm" aria-label="Main">
                <a href="{{ route('admin.dashboard') }}" @click="drawer = false" aria-current="{{ request()->routeIs('admin.dashboard') ? 'page' : 'false' }}"
                    class="flex items-center gap-2.5 rounded-lg px-3 py-2 {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-50 font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">
                    <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>
                    Overview
                </a>

                <p class="px-3 pb-1 pt-4 text-xs font-medium uppercase tracking-wider text-gray-400">Content</p>
                @foreach ([['route' => 'admin.projects.index', 'routeIs' => 'admin.projects.*', 'label' => 'Projects', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />'], ['route' => 'admin.blogs.index', 'routeIs' => 'admin.blogs.*', 'label' => 'Blogs', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />'], ['route' => 'admin.experiences.index', 'routeIs' => 'admin.experiences.*', 'label' => 'Experience', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />'], ['route' => 'admin.metrics.index', 'routeIs' => 'admin.metrics.*', 'label' => 'Metrics', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />'], ['route' => 'admin.skills.index', 'routeIs' => 'admin.skills.*', 'label' => 'Skills', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17l-5.384 3.18A1.125 1.125 0 014.5 17.31V5.69c0-.4.213-.77.559-.97l5.384-3.18a1.125 1.125 0 011.122 0l5.384 3.18c.346.2.559.57.559.97v11.62c0 .4-.213.77-.559.97l-5.384 3.18a1.125 1.125 0 01-1.122 0z" />'], ['route' => 'admin.tech-skills.index', 'routeIs' => 'admin.tech-skills.*', 'label' => 'Tech Skills', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />']] as $item)
                    <a href="{{ route($item['route']) }}" @click="drawer = false" aria-current="{{ request()->routeIs($item['routeIs']) ? 'page' : 'false' }}"
                        class="flex items-center gap-2.5 rounded-lg px-3 py-2 {{ request()->routeIs($item['routeIs']) ? 'bg-indigo-50 font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">
                        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $item['icon'] !!}</svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach

                <p class="px-3 pb-1 pt-4 text-xs font-medium uppercase tracking-wider text-gray-400">System</p>
                @foreach ([['route' => 'admin.activity.index', 'routeIs' => 'admin.activity.*', 'label' => 'Activity', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />']] as $item)
                    <a href="{{ route($item['route']) }}" @click="drawer = false" aria-current="{{ request()->routeIs($item['routeIs']) ? 'page' : 'false' }}"
                        class="flex items-center gap-2.5 rounded-lg px-3 py-2 {{ request()->routeIs($item['routeIs']) ? 'bg-indigo-50 font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300' : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5' }}">
                        <svg class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $item['icon'] !!}</svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </aside>

        {{-- ═══ Main column (row item next to the sidebar ≥ lg; sole item below,
             where the sidebar is position:fixed and out of flow) ═══ --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 flex h-14 items-center gap-2 border-b border-gray-200 bg-white px-4 dark:border-gray-800 dark:bg-gray-900 lg:px-6">
                <button type="button" @click="drawer = true" aria-label="Open menu" :aria-expanded="drawer" aria-controls="sidebar"
                    class="grid size-9 place-items-center rounded-lg hover:bg-gray-100 lg:hidden dark:hover:bg-gray-800">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>

                <div class="ml-auto flex items-center gap-1">
                    <button type="button" @click="darkMode = !darkMode" :aria-label="darkMode ? 'Switch to light mode' : 'Switch to dark mode'"
                        class="grid size-9 place-items-center rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                        <svg x-show="!darkMode" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" /></svg>
                        <svg x-show="darkMode" x-cloak class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" /></svg>
                    </button>

                    {{-- Account menu --}}
                    <div class="relative" x-data="{ open: false }" @click.away="open = false" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="menu"
                            class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800">
                            <span class="grid size-7 place-items-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300">{{ substr(auth()->user()->name ?? 'A', 0, 1) }}</span>
                            <span class="hidden text-sm font-medium sm:block">{{ auth()->user()->name ?? 'Admin' }}</span>
                        </button>
                        <div x-show="open" x-cloak x-transition.opacity.duration.100ms role="menu"
                            class="absolute right-0 z-50 mt-2 w-48 rounded-xl border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-800 dark:bg-gray-900">
                            <p class="truncate px-3 py-2 text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email ?? '' }}</p>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" role="menuitem" class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/5">Log out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main id="main" class="mx-auto w-full max-w-[1400px] flex-1 space-y-4 p-4 sm:p-6">
                {{-- Degraded-mode banner: shown whenever admin mutations are
                     unavailable — either Mongo fully unreachable (mock data) or a
                     primary partition (Eloquent reads AND writes need the primary). --}}
                @if (! $mongoWritable)
                    <div x-data="{ hidden: sessionStorage.getItem('mockBannerHidden') === '1' }" x-show="!hidden" x-cloak role="status"
                        class="flex items-center gap-2.5 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-500/25 dark:bg-amber-500/10 dark:text-amber-300">
                        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                        <span class="flex-1">@if ($mongoAvailable) MongoDB writes are <strong>temporarily unavailable</strong> (primary unreachable) — viewing read-only sample data. @else MongoDB is unreachable — you're viewing <strong>sample data</strong>. Changes can't be saved right now. @endif</span>
                        <button type="button" @click="hidden = true; sessionStorage.setItem('mockBannerHidden', '1')" aria-label="Dismiss" class="rounded p-1 hover:bg-amber-100 dark:hover:bg-amber-500/20">
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                @endif

                @if (session('success'))
                    <div role="status" class="flex items-center gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800 dark:border-emerald-500/25 dark:bg-emerald-500/10 dark:text-emerald-300">
                        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div role="alert" class="flex items-center gap-2.5 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800 dark:border-red-500/25 dark:bg-red-500/10 dark:text-red-300">
                        <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" /></svg>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
