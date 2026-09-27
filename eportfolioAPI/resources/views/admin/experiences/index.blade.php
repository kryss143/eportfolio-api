@extends('admin.layouts.app')

@section('title', 'Experience')

@section('content')
<div class="space-y-4">
    {{-- Page header --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-xl font-semibold">Experience</h1>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Manage your experience summary.</p>
        </div>
        <a href="{{ route('admin.experiences.create') }}" class="inline-flex h-9 items-center gap-2 rounded-lg bg-indigo-600 px-4 text-sm font-medium text-white hover:bg-indigo-700">
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add experience
        </a>
    </div>

    {{-- Data table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
        @if ($experiences->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs uppercase tracking-wide text-gray-500 dark:border-gray-800 dark:text-gray-400">
                            <th scope="col" class="px-4 py-2.5 font-medium">Position</th>
                            <th scope="col" class="px-4 py-2.5 text-right font-medium">Years</th>
                            <th scope="col" class="px-4 py-2.5 text-right font-medium">Solo projects</th>
                            <th scope="col" class="px-4 py-2.5 text-right font-medium">Collab projects</th>
                            <th scope="col" class="px-4 py-2.5 font-medium"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach ($experiences as $exp)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-2.5 font-medium">{{ $exp->position }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ $exp->yearsOfExperience }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ $exp->soloProjects }}</td>
                                <td class="px-4 py-2.5 text-right tabular-nums">{{ $exp->collabProjects }}</td>
                                <td class="px-4 py-2.5">
                                    @if ($mongoWritable)
                                        <a href="{{ route('admin.experiences.edit', $exp) }}" class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/5 dark:hover:text-gray-200" aria-label="Edit {{ $exp->position }}">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" /></svg>
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400" title="Saving unavailable — {{ $mongoAvailable ? 'primary unreachable' : 'MongoDB unreachable' }}">Read-only</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                No experience records yet.
                <a href="{{ route('admin.experiences.create') }}" class="ml-1 font-medium text-indigo-600 hover:underline dark:text-indigo-400">Add your first record</a>
            </div>
        @endif
    </div>
</div>
@endsection
