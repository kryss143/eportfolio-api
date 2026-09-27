@extends('admin.layouts.app')

@section('title', 'Activity Log')

@section('content')
<div class="space-y-4">
    {{-- Page header --}}
    <div>
        <h1 class="text-xl font-semibold">Activity Log</h1>
        <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Audit trail of changes made in the admin.</p>
    </div>

    {{-- Data table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        {{-- Toolbar --}}
        <form method="GET" class="flex flex-wrap items-center gap-2 border-b border-gray-200 p-3 dark:border-gray-800">
            <label class="sr-only" for="event">Event</label>
            <select id="event" name="event" class="h-9 rounded-lg border border-gray-300 bg-white px-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                <option value="">All events</option>
                <option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Created</option>
                <option value="updated" {{ request('event') === 'updated' ? 'selected' : '' }}>Updated</option>
                <option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Deleted</option>
            </select>
            <label class="sr-only" for="subject_type">Model</label>
            <select id="subject_type" name="subject_type" class="h-9 rounded-lg border border-gray-300 bg-white px-2 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                <option value="">All models</option>
                @foreach (['App\Models\Blog', 'App\Models\Project', 'App\Models\TechSkill', 'App\Models\Experience', 'App\Models\Metric'] as $type)
                    <option value="{{ $type }}" {{ request('subject_type') === $type ? 'selected' : '' }}>{{ class_basename($type) }}</option>
                @endforeach
            </select>
            <button type="submit" class="h-9 rounded-lg border border-gray-300 px-3 text-sm font-medium hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-white/5">Filter</button>
        </form>

        @if ($activities->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            <th scope="col" class="px-4 py-2.5 font-medium">User</th>
                            <th scope="col" class="px-4 py-2.5 font-medium">Event</th>
                            <th scope="col" class="px-4 py-2.5 font-medium">Model</th>
                            <th scope="col" class="px-4 py-2.5 font-medium">Details</th>
                            <th scope="col" class="px-4 py-2.5 text-right font-medium">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($activities as $activity)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-2.5 font-medium">{{ $activity->user?->name ?? 'System' }}</td>
                                <td class="px-4 py-2.5">
                                    <x-pill :tone="match($activity->event) { 'created' => 'success', 'updated' => 'info', 'deleted' => 'danger', default => 'neutral' }">{{ $activity->event }}</x-pill>
                                </td>
                                <td class="px-4 py-2.5">{{ class_basename($activity->subject_type) }}</td>
                                <td class="max-w-xs truncate px-4 py-2.5 text-gray-500 dark:text-gray-400">
                                    @if ($activity->event === 'updated' && $activity->new_values)
                                        Changed: {{ implode(', ', array_keys($activity->new_values)) }}
                                    @elseif ($activity->event === 'created')
                                        New record
                                    @else
                                        Deleted
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-right text-gray-500 dark:text-gray-400">{{ $activity->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($activities->hasPages())
                <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-800">{{ $activities->links() }}</div>
            @endif
        @else
            <div class="p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                @if (request('event') || request('subject_type'))
                    No activity matches these filters.
                    <a href="{{ route('admin.activity.index') }}" class="ml-1 font-medium text-indigo-600 hover:underline dark:text-indigo-400">Clear filters</a>
                @else
                    No activity recorded yet.
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
