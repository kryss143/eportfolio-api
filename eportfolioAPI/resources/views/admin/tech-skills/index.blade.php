@extends('admin.layouts.app')
@section('title', 'Tech Skills')
@section('content')
<div class="space-y-4">
    <div class="flex items-end justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-semibold">Tech Skills</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $techSkills->total() }} skill(s)</p>
        </div>
        <a href="{{ route('admin.tech-skills.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add skill
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search skills..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>
            <select name="category" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
                <option value="">All categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->value }}" {{ request('category') === $cat->value ? 'selected' : '' }}>{{ ucfirst($cat->value) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Filter</button>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        @if ($techSkills->count() > 0)
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>Label</th><th>Category</th><th>Logo</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach ($techSkills as $skill)
                            <tr>
                                <td class="font-medium">{{ $skill->label }}</td>
                                <td>
                                    <span class="status-pill status-pill-info"><span>{{ ucfirst($skill->category?->value ?? $skill->category) }}</span></span>
                                </td>
                                <td class="text-xs text-gray-400 font-mono max-w-xs truncate">{{ $skill->logo }}</td>
                                <td>
                                    <div class="flex items-center justify-start gap-1">
                                        @if ($mongoWritable)
                                        <a href="{{ route('admin.tech-skills.edit', $skill) }}" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                                        </a>
                                        <form method="POST" action="{{ route('admin.tech-skills.destroy', $skill) }}" onsubmit="return confirm('Delete this skill?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
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
            @if ($techSkills->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $techSkills->links() }}</div>
            @endif
        @else
            <div class="px-6 py-12 text-center text-sm text-gray-500">No tech skills found.</div>
        @endif
    </div>
</div>
@endsection
