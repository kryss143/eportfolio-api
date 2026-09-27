@extends('admin.layouts.app')

@section('title', 'Overview')

@section('content')
@php
    $maxCategoryCount = $skillsByCategory ? max($skillsByCategory) : 0;
    $draftBlogs = $recentBlogs->filter(fn ($blog) => empty($blog->date))->count();
@endphp
<div class="space-y-4">
    {{-- Page header --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold">Overview</h1>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Portfolio content at a glance. Updated {{ now()->diffForHumans() }}.</p>
        </div>
    </div>

    {{-- KPI row --}}
    <section aria-label="Key metrics" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-sm text-gray-500 dark:text-gray-400">Projects</div>
            <div class="mt-1 text-2xl font-semibold tabular-nums">{{ $stats['projects'] }}</div>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $projectsByStatus['built'] }} built, {{ $projectsByStatus['in-progress'] }} in progress</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-sm text-gray-500 dark:text-gray-400">Blog posts</div>
            <div class="mt-1 text-2xl font-semibold tabular-nums">{{ $stats['blogs'] }}</div>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $draftBlogs > 0 ? $draftBlogs.' draft(s) to publish' : 'All published' }}</div>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-gray-800 dark:bg-gray-900">
            <div class="text-sm text-gray-500 dark:text-gray-400">Tech skills</div>
            <div class="mt-1 text-2xl font-semibold tabular-nums">{{ $stats['tech_skills'] }}</div>
            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ count($skillsByCategory) }} categor{{ count($skillsByCategory) === 1 ? 'y' : 'ies' }}</div>
        </div>
    </section>

    {{-- Chart (2/3) + needs attention (1/3) --}}
    <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">
        <section aria-labelledby="skills-h" class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 lg:col-span-2">
            <div class="border-b border-gray-200 p-4 dark:border-gray-800">
                <h2 id="skills-h" class="text-sm font-semibold">Tech skills by category ({{ $stats['tech_skills'] }} total)</h2>
            </div>
            @if ($maxCategoryCount > 0)
                <div class="p-4">
                    {{-- Bar chart in plain utilities: bars start at zero, ≤ one series. --}}
                    <div class="flex h-44 items-end gap-3" role="img" aria-label="Bar chart of tech skill counts per category">
                        @foreach ($skillsByCategory as $category => $count)
                            <div class="flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-1.5">
                                <span class="text-sm font-medium tabular-nums">{{ $count }}</span>
                                <div class="w-full max-w-16 rounded-t-md bg-indigo-500/80 hover:bg-indigo-500 dark:bg-indigo-500/70 dark:hover:bg-indigo-400"
                                    style="height: {{ max(4, (int) round($count / $maxCategoryCount * 100)) }}%"></div>
                                <span class="w-full truncate text-center text-xs text-gray-500 capitalize dark:text-gray-400" title="{{ ucfirst($category) }}">{{ $category }}</span>
                            </div>
                        @endforeach
                    </div>
                    {{-- Accessible alternative to the chart --}}
                    <table class="sr-only">
                        <caption>Tech skills per category</caption>
                        <thead><tr><th scope="col">Category</th><th scope="col">Skills</th></tr></thead>
                        <tbody>
                            @foreach ($skillsByCategory as $category => $count)
                                <tr><td>{{ ucfirst($category) }}</td><td class="tabular-nums">{{ $count }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    No tech skills yet.
                    <a href="{{ route('admin.tech-skills.create') }}" class="ml-1 font-medium text-indigo-600 hover:underline dark:text-indigo-400">Add your first skill</a>
                </div>
            @endif
        </section>

        <section aria-labelledby="attention-h" class="rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 p-4 dark:border-gray-800">
                <h2 id="attention-h" class="text-sm font-semibold">Needs attention</h2>
            </div>
            @if (count($needsAttention) > 0)
                <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ($needsAttention as $item)
                        <li>
                            <a href="{{ $item['link'] }}" class="flex items-center justify-between gap-2 p-4 text-sm hover:bg-gray-50 dark:hover:bg-white/5">
                                <span>{{ $item['message'] }}</span>
                                <x-pill :tone="$item['type']">{{ ucfirst($item['type']) }}</x-pill>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-10 text-center text-sm text-gray-500 dark:text-gray-400">Everything looks good!</div>
            @endif
        </section>
    </div>

    {{-- Recent blogs + recent activity --}}
    <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
        <section aria-labelledby="recent-blogs-h" class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-800">
                <h2 id="recent-blogs-h" class="text-sm font-semibold">Recent blog posts</h2>
                <a href="{{ route('admin.blogs.index') }}" class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">View all</a>
            </div>
            @if ($recentBlogs->count() > 0)
                <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ($recentBlogs as $blog)
                        <li class="flex items-center justify-between gap-3 p-4">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ $blog->title }}</p>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $blog->date ? \Carbon\Carbon::parse($blog->date)->format('M d, Y') : 'Unpublished draft' }}</p>
                            </div>
                            <x-pill :tone="$blog->date ? 'success' : 'neutral'">{{ $blog->date ? 'Published' : 'Draft' }}</x-pill>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                    No blog posts yet.
                    <a href="{{ route('admin.blogs.create') }}" class="ml-1 font-medium text-indigo-600 hover:underline dark:text-indigo-400">Write your first post</a>
                </div>
            @endif
        </section>

        <section aria-labelledby="recent-activity-h" class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 p-4 dark:border-gray-800">
                <h2 id="recent-activity-h" class="text-sm font-semibold">Recent activity</h2>
                <a href="{{ route('admin.activity.index') }}" class="text-xs font-medium text-indigo-600 hover:underline dark:text-indigo-400">View all</a>
            </div>
            @if ($recentActivity->count() > 0)
                <ul class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ($recentActivity as $activity)
                        <li class="p-4 text-sm">
                            <p>
                                <span class="font-medium">{{ $activity->user?->name ?? 'System' }}</span>
                                <span class="text-gray-500 dark:text-gray-400">{{ $activity->event }} {{ class_basename($activity->subject_type) }}</span>
                            </p>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $activity->created_at->diffForHumans() }}</p>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="p-10 text-center text-sm text-gray-500 dark:text-gray-400">No activity yet.</div>
            @endif
        </section>
    </div>
</div>
@endsection
