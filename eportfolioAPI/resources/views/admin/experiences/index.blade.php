@extends('admin.layouts.app')
@section('title', 'Experience')
@section('content')
<div class="space-y-4">
    <div class="flex items-end justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-semibold">Experience</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your experience summary.</p>
        </div>
        <a href="{{ route('admin.experiences.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add experience
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        @if ($experiences->count() > 0)
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>ID</th><th>Position</th><th>Years</th><th>Solo Projects</th><th>Collab Projects</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach ($experiences as $exp)
                            <tr>
                                <td class="font-mono text-sm">{{ $exp->id }}</td>
                                <td class="font-medium">{{ $exp->position }}</td>
                                <td class="tabular-nums">{{ $exp->yearsOfExperience }}</td>
                                <td class="tabular-nums">{{ $exp->soloProjects }}</td>
                                <td class="tabular-nums">{{ $exp->collabProjects }}</td>
                                <td>
                                    @if ($mongoAvailable)
                                    <a href="{{ route('admin.experiences.edit', $exp) }}" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                                    </a>
                                    @else
                                    <span class="text-xs text-gray-400" title="Mock data is read-only">Read-only</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-12 text-center text-sm text-gray-400">No experience records found.</div>
        @endif
    </div>
</div>
@endsection
