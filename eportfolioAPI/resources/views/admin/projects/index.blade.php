@extends('admin.layouts.app')

@section('title', 'Projects')

@section('content')
<div class="space-y-4">
    {{-- Page header --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold">Projects</h1>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $projects->total() }} project(s) total</p>
        </div>
        <a href="{{ route('admin.projects.create') }}" class="inline-flex h-9 items-center gap-2 rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white hover:bg-indigo-700">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add project
        </a>
    </div>

    {{-- Data table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        {{-- Toolbar --}}
        <form method="GET" class="flex flex-wrap items-center gap-2 border-b border-gray-200 p-3 dark:border-gray-800">
            <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Search projects…" aria-label="Search projects"
                class="h-9 w-64 rounded-lg border border-gray-300 bg-transparent px-3 text-sm placeholder:text-gray-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700">
            <label class="sr-only" for="status">Status</label>
            <select id="status" name="status" class="h-9 rounded-lg border border-gray-300 bg-white px-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                <option value="">All statuses</option>
                <option value="built" {{ request('status') === 'built' ? 'selected' : '' }}>Built</option>
                <option value="in-progress" {{ request('status') === 'in-progress' ? 'selected' : '' }}>In progress</option>
            </select>
            <button type="submit" class="h-9 rounded-lg border border-gray-300 px-3 text-sm font-medium hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-white/5">Filter</button>
            @if (request('search') || request('status'))
                <a href="{{ route('admin.projects.index') }}" class="h-9 rounded-lg px-3 text-sm leading-9 text-gray-500 hover:underline dark:text-gray-400">Clear</a>
            @endif
        </form>

        {{-- Active filter chips --}}
        @if (request('search') || request('status'))
            <div class="flex flex-wrap items-center gap-1.5 border-b border-gray-200 p-3 dark:border-gray-800">
                @if (request('search'))
                    <span class="inline-flex items-center gap-1 rounded-full border border-gray-300 bg-gray-100 px-2.5 py-0.5 text-xs dark:border-gray-700 dark:bg-gray-800">
                        Search: {{ request('search') }}
                        <a href="{{ request()->except('search') }}" class="hover:text-red-500" aria-label="Remove search filter">&times;</a>
                    </span>
                @endif
                @if (request('status'))
                    <span class="inline-flex items-center gap-1 rounded-full border border-gray-300 bg-gray-100 px-2.5 py-0.5 text-xs dark:border-gray-700 dark:bg-gray-800">
                        Status: {{ request('status') }}
                        <a href="{{ request()->except('status') }}" class="hover:text-red-500" aria-label="Remove status filter">&times;</a>
                    </span>
                @endif
            </div>
        @endif

        @if ($projects->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            <th scope="col" class="px-4 py-2.5 font-medium">Project</th>
                            <th scope="col" class="px-4 py-2.5 font-medium">Status</th>
                            <th scope="col" class="px-4 py-2.5 font-medium">Featured</th>
                            <th scope="col" class="px-4 py-2.5 font-medium">Technologies</th>
                            <th scope="col" class="px-4 py-2.5 text-right font-medium">Created</th>
                            <th scope="col" class="px-4 py-2.5 font-medium"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($projects as $project)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-2.5">
                                    <div class="font-medium">{{ $project->title }}</div>
                                    <div class="max-w-xs truncate text-xs text-gray-500 dark:text-gray-400" title="{{ $project->description }}">{{ Str::limit($project->description, 60) }}</div>
                                </td>
                                <td class="px-4 py-2.5">
                                    <x-pill :tone="$project->status === 'built' ? 'success' : 'warning'">{{ $project->status === 'built' ? 'Built' : 'In progress' }}</x-pill>
                                </td>
                                <td class="px-4 py-2.5">
                                    @if ($project->featured)
                                        <x-pill tone="info">Featured</x-pill>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5">
                                    <div class="flex max-w-xs flex-wrap gap-1">
                                        @foreach (array_slice($project->technologies ?? [], 0, 3) as $tech)
                                            <span class="rounded bg-gray-100 px-1.5 py-0.5 text-xs dark:bg-gray-800">{{ $tech }}</span>
                                        @endforeach
                                        @if (count($project->technologies ?? []) > 3)
                                            <span class="px-1.5 py-0.5 text-xs text-gray-400">+{{ count($project->technologies) - 3 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-right tabular-nums text-gray-500 dark:text-gray-400">{{ $project->created_at?->format('M d, Y') ?? '—' }}</td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-1">
                                        @if ($mongoWritable)
                                        <a href="{{ route('admin.projects.edit', $project) }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-gray-200" aria-label="Edit project">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Delete this project?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10" aria-label="Delete project">
                                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-xs text-gray-400" title="Saving unavailable — {{ $mongoAvailable ? 'primary unreachable' : 'MongoDB unreachable' }}">Read-only</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($projects->hasPages())
                <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">
                    {{ $projects->links() }}
                </div>
            @endif
        @else
            <div class="p-10 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if (request('search') || request('status'))
                        No projects match these filters.
                        <a href="{{ route('admin.projects.index') }}" class="ml-1 font-medium text-indigo-600 hover:underline dark:text-indigo-400">Clear filters</a>
                    @else
                        No projects yet.
                        <a href="{{ route('admin.projects.create') }}" class="ml-1 font-medium text-indigo-600 hover:underline dark:text-indigo-400">Create your first project</a>
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>
@endsection
