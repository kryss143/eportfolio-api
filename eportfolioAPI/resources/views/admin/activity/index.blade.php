@extends('admin.layouts.app')
@section('title', 'Activity Log')
@section('content')
<div class="space-y-4">
    <div class="mb-2">
        <h1 class="text-2xl font-semibold">Activity Log</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Audit trail of changes made in the admin.</p>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Event</label>
                <select name="event" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
                    <option value="">All events</option>
                    <option value="created" {{ request('event') === 'created' ? 'selected' : '' }}>Created</option>
                    <option value="updated" {{ request('event') === 'updated' ? 'selected' : '' }}>Updated</option>
                    <option value="deleted" {{ request('event') === 'deleted' ? 'selected' : '' }}>Deleted</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Model</label>
                <select name="subject_type" class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
                    <option value="">All models</option>
                    @foreach (['App\Models\Blog', 'App\Models\Project', 'App\Models\TechSkill', 'App\Models\Experience', 'App\Models\Metric'] as $type)
                        <option value="{{ $type }}" {{ request('subject_type') === $type ? 'selected' : '' }}>{{ class_basename($type) }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Filter</button>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        @if ($activities->count() > 0)
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>User</th><th>Event</th><th>Model</th><th>Details</th><th>Time</th></tr></thead>
                    <tbody>
                        @foreach ($activities as $activity)
                            <tr>
                                <td class="font-medium">{{ $activity->user?->name ?? 'System' }}</td>
                                <td>
                                    <span class="status-pill {{ match($activity->event) { 'created' => 'status-pill-success', 'updated' => 'status-pill-info', 'deleted' => 'status-pill-danger', default => 'status-pill-neutral' } }}">
                                        <span>{{ $activity->event }}</span>
                                    </span>
                                </td>
                                <td class="text-sm">{{ class_basename($activity->subject_type) }}</td>
                                <td class="text-xs text-gray-400 max-w-xs truncate">
                                    @if ($activity->event === 'updated' && $activity->new_values)
                                        Changed: {{ implode(', ', array_keys($activity->new_values)) }}
                                    @elseif ($activity->event === 'created')
                                        New record
                                    @else
                                        Deleted
                                    @endif
                                </td>
                                <td class="text-sm text-gray-500">{{ $activity->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($activities->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $activities->links() }}</div>
            @endif
        @else
            <div class="px-6 py-12 text-center text-sm text-gray-400">No activity recorded yet.</div>
        @endif
    </div>
</div>
@endsection
