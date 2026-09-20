@extends('admin.layouts.app')
@section('title', 'Skills')
@section('content')
<div class="space-y-4">
    <div class="flex items-end justify-between gap-4 flex-wrap">
        <div>
            <h1 class="text-2xl font-semibold">Skills</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Skill categories for your portfolio.</p>
        </div>
        <a href="{{ route('admin.skills.edit') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" /></svg>
            Edit skills
        </a>
    </div>

    @if ($skills)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach (['proficient', 'familiar', 'authentication', 'architecture', 'toolsPlatforms', 'practices', 'ai'] as $category)
                <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5">
                    <h3 class="text-sm font-semibold capitalize mb-3">{{ str_replace('_', ' ', $category) }}</h3>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($skills->$category as $skill)
                            <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs">{{ $skill }}</span>
                        @endforeach
                        @if (count($skills->$category ?? []) === 0)
                            <span class="text-xs text-gray-400">No skills in this category.</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-8 text-center">
            <p class="text-sm text-gray-500 mb-2">No skills configured yet.</p>
            <a href="{{ route('admin.skills.edit') }}" class="text-sm text-indigo-600 hover:underline">Set up skills</a>
        </div>
    @endif
</div>
@endsection
