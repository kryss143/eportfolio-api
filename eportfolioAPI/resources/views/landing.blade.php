<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kryss — Full-Stack Developer Portfolio</title>
    <meta name="description" content="Full-stack developer portfolio showcasing projects, skills, and blog posts.">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/css/landing.css'])
    @endif
    <style>
        html { scroll-behavior: smooth; }
        .fade-in { opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .fade-in.visible { opacity: 1; transform: translateY(0); }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 antialiased">

    {{-- Nav --}}
    <nav class="fixed top-0 w-full z-50 bg-gray-950/80 backdrop-blur-md border-b border-gray-800/50">
        <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
            <a href="#" class="font-bold text-lg">Kryss<span class="text-indigo-400">.</span></a>
            <div class="flex items-center gap-6 text-sm text-gray-400">
                <a href="#projects" class="hover:text-white transition-colors">Projects</a>
                <a href="#skills" class="hover:text-white transition-colors">Skills</a>
                <a href="#blog" class="hover:text-white transition-colors">Blog</a>
                <a href="{{ route('admin.login') }}" class="px-3 py-1.5 border border-gray-700 rounded-lg hover:border-indigo-400 hover:text-indigo-400 transition-colors text-xs">Admin</a>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section class="min-h-screen flex items-center justify-center px-6 pt-14">
        <div class="max-w-3xl text-center">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-500/10 border border-indigo-500/20 rounded-full text-indigo-400 text-xs font-medium mb-6">
                <span class="w-1.5 h-1.5 bg-indigo-400 rounded-full animate-pulse"></span>
                Available for opportunities
            </div>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight leading-tight">
                Building full-stack<br>
                <span class="text-indigo-400">web applications</span>
            </h1>
            <p class="mt-6 text-lg text-gray-400 max-w-xl mx-auto leading-relaxed">
                {{ $experience['position'] ?? 'Full-Stack Developer' }} with {{ $experience['yearsOfExperience'] ?? 2 }}+ years of experience. I build
                {{ $experience['soloProjects'] ?? 2 }} solo and {{ $experience['collabProjects'] ?? 2 }} collaborative projects across frontend, backend, and deployment.
            </p>

            {{-- Metrics --}}
            <div class="mt-10 grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-2xl mx-auto">
                @foreach ($metrics as $metric)
                    <div class="p-4 bg-gray-900/50 border border-gray-800 rounded-xl">
                        <div class="text-2xl font-bold tabular-nums text-white">{{ number_format($metric['value']) }}{{ $metric['suffix'] ?? '' }}</div>
                        <div class="text-xs text-gray-500 mt-1">{{ $metric['label'] }}</div>
                    </div>
                @endforeach
            </div>

            <div class="mt-10 flex items-center justify-center gap-4">
                <a href="#projects" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-colors">View projects</a>
                <a href="#blog" class="px-6 py-3 border border-gray-700 hover:border-gray-500 text-sm font-medium rounded-xl transition-colors">Read blog</a>
            </div>
        </div>
    </section>

    {{-- Projects --}}
    <section id="projects" class="py-24 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="mb-12">
                <h2 class="text-3xl font-bold">Projects</h2>
                <p class="text-gray-400 mt-2">A selection of things I've built.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($projects as $project)
                    <div class="group bg-gray-900/50 border border-gray-800 rounded-2xl overflow-hidden hover:border-gray-700 transition-all fade-in">
                        <div class="h-48 bg-gradient-to-br from-indigo-500/10 to-purple-500/10 flex items-center justify-center relative overflow-hidden">
                            <div class="text-4xl font-bold text-gray-800">{{ substr($project['title'], 0, 1) }}</div>
                            @if (($project['status'] ?? null) === 'in-progress')
                                <span class="absolute top-3 right-3 px-2 py-0.5 bg-amber-500/20 text-amber-400 text-xs rounded-full border border-amber-500/30">In Progress</span>
                            @endif
                            @if ($project['featured'] ?? false)
                                <span class="absolute top-3 left-3 px-2 py-0.5 bg-indigo-500/20 text-indigo-400 text-xs rounded-full border border-indigo-500/30">Featured</span>
                            @endif
                        </div>
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-2">{{ $project['title'] }}</h3>
                            <p class="text-sm text-gray-400 leading-relaxed mb-4">{{ Str::limit($project['description'], 120) }}</p>
                            <div class="flex flex-wrap gap-1.5 mb-4">
                                @foreach (array_slice($project['technologies'] ?? [], 0, 4) as $tech)
                                    <span class="px-2 py-0.5 bg-gray-800 text-gray-300 text-xs rounded-md">{{ $tech }}</span>
                                @endforeach
                                @if (count($project['technologies'] ?? []) > 4)
                                    <span class="px-2 py-0.5 text-gray-500 text-xs">+{{ count($project['technologies']) - 4 }}</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                @if ($project['githubLink'] ?? null)
                                    <a href="{{ $project['githubLink'] }}" target="_blank" class="text-gray-400 hover:text-white transition-colors flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                                        Code
                                    </a>
                                @endif
                                @if ($project['demoLink'] ?? null)
                                    <a href="{{ $project['demoLink'] }}" target="_blank" class="text-indigo-400 hover:text-indigo-300 transition-colors flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                        Live demo
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Skills --}}
    <section id="skills" class="py-24 px-6 bg-gray-900/30">
        <div class="max-w-6xl mx-auto">
            <div class="mb-12">
                <h2 class="text-3xl font-bold">Skills & Technologies</h2>
                <p class="text-gray-400 mt-2">Tools and technologies I work with.</p>
            </div>

            @if ($skills)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach (['proficient' => 'Proficient', 'familiar' => 'Familiar', 'authentication' => 'Authentication', 'architecture' => 'Architecture', 'toolsPlatforms' => 'Tools & Platforms', 'practices' => 'Practices', 'ai' => 'AI Tools'] as $key => $label)
                        @if (count($skills[$key] ?? []) > 0)
                            <div class="bg-gray-900/50 border border-gray-800 rounded-xl p-5">
                                <h3 class="text-sm font-semibold text-indigo-400 mb-3">{{ $label }}</h3>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($skills[$key] as $skill)
                                        <span class="px-2 py-0.5 bg-gray-800 text-gray-300 text-xs rounded-md">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif

            {{-- Tech skill icons --}}
            @if ($groupedSkills->count() > 0)
                <div class="mt-12">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-6">Tech Stack</h3>
                    @foreach ($groupedSkills as $category => $catSkills)
                        <div class="mb-6">
                            <h4 class="text-xs text-gray-500 uppercase tracking-wider mb-3 capitalize">{{ $category }}</h4>
                            <div class="flex flex-wrap gap-3">
                                @foreach ($catSkills as $skill)
                                    <div class="flex items-center gap-2 px-3 py-2 bg-gray-900/50 border border-gray-800 rounded-lg hover:border-gray-700 transition-colors">
                                        @if ($skill['logo'])
                                            <img src="{{ $skill['logo'] }}" alt="" class="w-5 h-5" onerror="this.style.display='none'">
                                        @endif
                                        <span class="text-sm text-gray-300">{{ $skill['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Blog --}}
    <section id="blog" class="py-24 px-6">
        <div class="max-w-6xl mx-auto">
            <div class="mb-12">
                <h2 class="text-3xl font-bold">Blog</h2>
                <p class="text-gray-400 mt-2">Thoughts on development, architecture, and tools.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($blogs as $blog)
                    <article class="bg-gray-900/50 border border-gray-800 rounded-xl p-6 hover:border-gray-700 transition-all fade-in">
                        <div class="flex items-center gap-2 text-xs text-gray-500 mb-3">
                            <time>{{ \Carbon\Carbon::parse($blog['date'])->format('M d, Y') }}</time>
                            <span>·</span>
                            <span>{{ $blog['readTime'] }}</span>
                        </div>
                        <h3 class="font-semibold mb-2 leading-snug">{{ $blog['title'] }}</h3>
                        <p class="text-sm text-gray-400 leading-relaxed">{{ Str::limit($blog['excerpt'], 100) }}</p>
                        <div class="mt-4">
                            <span class="text-sm text-indigo-400 hover:text-indigo-300 transition-colors">Read more →</span>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="py-12 px-6 border-t border-gray-800/50">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-sm text-gray-500">© {{ date('Y') }} Kryss. Built with Laravel + Tailwind CSS.</p>
            <div class="flex items-center gap-4 text-sm text-gray-500">
                <a href="{{ route('admin.login') }}" class="hover:text-white transition-colors">Admin</a>
                <a href="/api/v1/projects" class="hover:text-white transition-colors">API</a>
            </div>
        </div>
    </footer>

    <script>
        // Fade-in on scroll
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));
    </script>
</body>
</html>
