@extends('admin.layouts.app')
@section('title', 'Edit Skills')
@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Edit Skills</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">One skill per line in each category.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
            <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.skills.update') }}" class="space-y-5">
        @csrf
        @method('PUT')
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 space-y-5">
            @foreach (['proficient', 'familiar', 'authentication', 'architecture', 'toolsPlatforms', 'practices', 'ai'] as $category)
                <div>
                    <label for="{{ $category }}" class="block text-sm font-semibold capitalize mb-1">{{ str_replace('_', ' ', $category) }}</label>
                    <textarea id="{{ $category }}" name="{{ $category }}[]" rows="4"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-indigo-500"
                        placeholder="One skill per line">{{ implode("\n", $skills->$category ?? []) }}</textarea>
                    <p class="text-xs text-gray-400 mt-1">One skill per line.</p>
                </div>
            @endforeach
        </div>
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.skills.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Save changes</button>
        </div>
    </form>
</div>
@endsection
