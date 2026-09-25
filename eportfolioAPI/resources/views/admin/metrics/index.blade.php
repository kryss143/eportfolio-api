@extends('admin.layouts.app')
@section('title', 'Metrics')
@section('content')
<div class="space-y-4">
    <div class="flex items-end justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-semibold">Metrics</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Portfolio stats displayed on the homepage.</p>
        </div>
        <a href="{{ route('admin.metrics.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            Add metric
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($metrics as $metric)
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5">
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ $metric->label }}</div>
                <div class="text-3xl font-semibold mt-1 tabular-nums">{{ number_format($metric->value) }}{{ $metric->suffix ?? '' }}</div>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">{{ $metric->metricDescription }}</p>
                @if ($mongoWritable)
                <a href="{{ route('admin.metrics.edit', $metric) }}" class="mt-3 inline-flex items-center gap-1 text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
                    Edit
                </a>
                @else
                <span class="mt-3 text-xs text-gray-400" title="Saving unavailable — {{ $mongoAvailable ? 'primary unreachable' : 'MongoDB unreachable' }}">Read-only</span>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection
