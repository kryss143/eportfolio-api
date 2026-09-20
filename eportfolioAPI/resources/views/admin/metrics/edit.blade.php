@extends('admin.layouts.app')
@section('title', 'Edit Metric')
@section('content')
<div class="max-w-lg">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Edit Metric</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">ID: {{ $metric->id }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
            <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.metrics.update', $metric) }}" class="space-y-5">
        @csrf
        @method('PUT')
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 space-y-4">
            <div>
                <label for="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Label</label>
                <input type="text" id="label" name="label" value="{{ old('label', $metric->label) }}" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="value" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Value</label>
                    <input type="number" id="value" name="value" value="{{ old('value', $metric->value) }}" min="0" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="suffix" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Suffix</label>
                    <input type="text" id="suffix" name="suffix" value="{{ old('suffix', $metric->suffix) }}" placeholder="e.g. +"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label for="metricDescription" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea id="metricDescription" name="metricDescription" rows="2" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">{{ old('metricDescription', $metric->metricDescription) }}</textarea>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.metrics.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Save changes</button>
        </div>
    </form>
</div>
@endsection
