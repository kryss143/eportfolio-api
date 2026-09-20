@extends('admin.layouts.app')

@section('title', 'Overview')

@section('content')
<div class="space-y-6">
    {{-- Page header --}}
    <div class="flex items-end justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-semibold">Overview</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Portfolio admin dashboard. Updated {{ now()->diffForHumans() }}.</p>
        </div>
    </div>

    {{-- KPI row --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="kpi-card">
            <div class="text-sm text-gray-500 dark:text-gray-400">Projects</div>
            <div class="text-2xl font-semibold mt-1 tabular-nums">{{ $stats['projects'] }}</div>
            <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $projectsByStatus['built'] }} built, {{ $projectsByStatus['in-progress'] }} in progress</div>
        </div>
        <div class="kpi-card">
            <div class="text-sm text-gray-500 dark:text-gray-400">Blog Posts</div>
            <div class="text-2xl font-semibold mt-1 tabular-nums">{{ $stats['blogs'] }}</div>
            <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">Published content</div>
        </div>
        <div class="kpi-card">
            <div class="text-sm text-gray-500 dark:text-gray-400">Tech Skills</div>
            <div class="text-2xl font-semibold mt-1 tabular-nums">{{ $stats['tech_skills'] }}</div>
            <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ count($skillsByCategory) }} categories</div>
        </div>
        <div class="kpi-card">
            <div class="text-sm text-gray-500 dark:text-gray-400">Users</div>
            <div class="text-2xl font-semibold mt-1 tabular-nums">{{ $stats['users'] }}</div>
            <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">Registered accounts</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Projects by status chart --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5">
            <h2 class="text-sm font-semibold mb-4">Projects by Status</h2>
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <div class="flex justify-between text-sm mb-1">
                            <span>Built</span>
                            <span class="tabular-nums">{{ $projectsByStatus['built'] }}</span>
                        </div>
                        <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500 rounded-full" style="width: {{ $stats['projects'] > 0 ? ($projectsByStatus['built'] / $stats['projects'] * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <div class="flex justify-between text-sm mb-1">
                            <span>In Progress</span>
                            <span class="tabular-nums">{{ $projectsByStatus['in-progress'] }}</span>
                        </div>
                        <div class="h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-500 rounded-full" style="width: {{ $stats['projects'] > 0 ? ($projectsByStatus['in-progress'] / $stats['projects'] * 100) : 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Skills by category --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5">
            <h2 class="text-sm font-semibold mb-4">Skills by Category</h2>
            <div class="space-y-2">
                @forelse ($skillsByCategory as $category => $count)
                    <div class="flex items-center justify-between text-sm">
                        <span class="capitalize">{{ $category }}</span>
                        <span class="tabular-nums text-gray-500 dark:text-gray-400">{{ $count }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No tech skills yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Needs attention --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5">
            <h2 class="text-sm font-semibold mb-4">Needs Attention</h2>
            @if (count($needsAttention) > 0)
                <div class="space-y-2">
                    @foreach ($needsAttention as $item)
                        <a href="{{ $item['link'] }}" class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 text-sm">
                            <span>{{ $item['message'] }}</span>
                            <span class="status-pill status-pill-{{ $item['type'] }}">{{ $item['type'] }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <p class="text-sm text-gray-400">Everything looks good!</p>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Recent blogs --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-sm font-semibold">Recent Blog Posts</h2>
                <a href="{{ route('admin.blogs.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">View all</a>
            </div>
            @if ($recentBlogs->count() > 0)
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach ($recentBlogs as $blog)
                        <div class="px-5 py-3 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium truncate">{{ $blog->title }}</p>
                                <p class="text-xs text-gray-400">{{ $blog->date ? (is_string($blog->date) ? \Carbon\Carbon::parse($blog->date)->format('M d, Y') : $blog->date->format('M d, Y')) : 'Draft' }}</p>
                            </div>
                            <span class="status-pill {{ $blog->date ? 'status-pill-success' : 'status-pill-neutral' }}">
                                <span>{{ $blog->date ? 'Published' : 'Draft' }}</span>
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-5 py-8 text-center text-sm text-gray-400">No blog posts yet.</div>
            @endif
        </div>

        {{-- Recent activity --}}
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg">
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-sm font-semibold">Recent Activity</h2>
                <a href="{{ route('admin.activity.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">View all</a>
            </div>
            @if ($recentActivity->count() > 0)
                <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                    @foreach ($recentActivity as $activity)
                        <div class="px-5 py-3 text-sm">
                            <p>
                                <span class="font-medium">{{ $activity->user?->name ?? 'System' }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $activity->event }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ class_basename($activity->subject_type) }}</span>
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="px-5 py-8 text-center text-sm text-gray-400">No activity yet.</div>
            @endif
        </div>
    </div>
</div>
@endsection
