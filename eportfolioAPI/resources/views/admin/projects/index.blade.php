@extends('admin.layouts.app')

@section('title', 'Projects')

@section('content')
<div class="space-y-4">
    {{-- Page header --}}
    <div class="flex items-end justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-semibold">Projects</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $projects->total() }} project(s) total</p>
        </div>
        <a href="{{ route('admin.projects.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add project
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label for="search" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Search</label>
                <input type="search" id="search" name="search" value="{{ request('search') }}" placeholder="Search projects..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label for="status" class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <select id="status" name="status" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    <option value="built" {{ request('status') === 'built' ? 'selected' : '' }}>Built</option>
                    <option value="in-progress" {{ request('status') === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Filter</button>
            @if (request('search') || request('status'))
                <a href="{{ route('admin.projects.index') }}" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">Clear</a>
            @endif
        </form>
    </div>

    {{-- Active filter chips --}}
    @if (request('search') || request('status'))
        <div class="flex flex-wrap gap-2">
            @if (request('search'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-xs font-medium">
                    Search: {{ request('search') }}
                    <a href="{{ request()->except('search') }}" class="hover:text-red-500" aria-label="Remove search filter">&times;</a>
                </span>
            @endif
            @if (request('status'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-gray-100 dark:bg-gray-700 rounded-full text-xs font-medium">
                    Status: {{ request('status') }}
                    <a href="{{ request()->except('status') }}" class="hover:text-red-500" aria-label="Remove status filter">&times;</a>
                </span>
            @endif
        </div>
    @endif

    {{-- Data table --}}
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        @if ($projects->count() > 0)
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Technologies</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($projects as $project)
                            <tr>
                                <td>
                                    <div class="font-medium">{{ $project->title }}</div>
                                    <div class="text-xs text-gray-400 truncate max-w-xs">{{ Str::limit($project->description, 60) }}</div>
                                </td>
                                <td>
                                    <span class="status-pill {{ $project->status === 'built' ? 'status-pill-success' : 'status-pill-warning' }}">
                                        <span>{{ $project->status }}</span>
                                    </span>
                                </td>
                                <td>
                                    @if ($project->featured)
                                        <span class="status-pill status-pill-info"><span>Featured</span></span>
                                    @else
                                        <span class="text-gray-400 text-sm">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @foreach (array_slice($project->technologies ?? [], 0, 3) as $tech)
                                            <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-700 rounded text-xs">{{ $tech }}</span>
                                        @endforeach
                                        @if (count($project->technologies ?? []) > 3)
                                            <span class="px-1.5 py-0.5 text-xs text-gray-400">+{{ count($project->technologies) - 3 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-sm text-gray-500 dark:text-gray-400">{{ $project->created_at?->format('M d, Y') ?? '—' }}</td>
                                <td>
                                    <div class="flex items-center justify-start gap-1">
                                        @if ($mongoAvailable)
                                        <a href="{{ route('admin.projects.edit', $project) }}" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" aria-label="Edit project">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" onsubmit="return confirm('Delete this project?')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" aria-label="Delete project">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                            </button>
                                        </form>
                                        @else
                                        <span class="text-xs text-gray-400" title="Mock data is read-only">Read-only</span>
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
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
                    {{ $projects->links() }}
                </div>
            @endif
        @else
            <div class="px-6 py-12 text-center">
                <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" /></svg>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">No projects found.</p>
                <a href="{{ route('admin.projects.create') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Create your first project</a>
            </div>
        @endif
    </div>
</div>
@endsection
