@extends('admin.layouts.app')
@section('title', 'Users')
@section('content')
<div class="space-y-4">
    <div class="mb-2">
        <h1 class="text-2xl font-semibold">Users</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $users->total() }} user(s)</p>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4">
        <form method="GET" class="flex items-end gap-3">
            <div class="flex-1">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Search users..."
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Search</button>
        </form>
    </div>

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        @if ($users->count() > 0)
            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="font-medium">
                                    {{ $user->name }}
                                    @if ($user->id === auth()->id())
                                        <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 align-middle" title="This is your account">You</span>
                                    @endif
                                </td>
                                <td class="text-sm text-gray-500">{{ $user->email }}</td>
                                <td>
                                    <span class="status-pill {{ $user->is_admin ? 'status-pill-success' : 'status-pill-neutral' }}">
                                        <span>{{ $user->is_admin ? 'Admin' : 'User' }}</span>
                                    </span>
                                </td>
                                <td class="text-sm text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                                <td>
                                    <div class="flex items-center justify-start gap-1">
                                        @if ($mongoAvailable)
                                        <a href="{{ route('admin.users.edit', $user) }}" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700" title="Edit user">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                                        </a>
                                        @if ($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="px-2 py-1 text-xs font-medium rounded-lg {{ $user->is_admin ? 'text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-900/20' : 'text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20' }}">
                                                    {{ $user->is_admin ? 'Revoke admin' : 'Make admin' }}
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete {{ $user->name }}? This cannot be undone.')" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" title="{{ $user->id === auth()->id() ? "You can't delete your own account" : 'Delete user' }}">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                            </button>
                                        </form>
                                        @endif
                                        @if (! $mongoAvailable)
                                        <span class="text-xs text-gray-400" title="Users live in SQLite, not MongoDB">SQLite-backed</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700">{{ $users->links() }}</div>
            @endif
        @else
            <div class="px-6 py-12 text-center text-sm text-gray-500">No users found.</div>
        @endif
    </div>
</div>
@endsection
