@extends('admin.layouts.app')

@section('title', 'Create Project')

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Create Project</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add a new project to your portfolio.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
            <p class="font-medium mb-1">Please fix the following errors:</p>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.projects.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 space-y-4">
            <h2 class="text-sm font-semibold border-b border-gray-200 dark:border-gray-700 pb-2">Basic Information</h2>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description <span class="text-red-500">*</span></label>
                <textarea id="description" name="description" rows="3" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
            </div>

            <div>
                <label for="outcome" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Outcome <span class="text-red-500">*</span></label>
                <textarea id="outcome" name="outcome" rows="2" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">{{ old('outcome') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status <span class="text-red-500">*</span></label>
                    <select id="status" name="status" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="built" {{ old('status') === 'built' ? 'selected' : '' }}>Built</option>
                        <option value="in-progress" {{ old('status') === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="featured" value="1" {{ old('featured') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-gray-700 dark:text-gray-300">Featured project</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 space-y-4">
            <h2 class="text-sm font-semibold border-b border-gray-200 dark:border-gray-700 pb-2">Links & Media</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="githubLink" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">GitHub URL</label>
                    <input type="url" id="githubLink" name="githubLink" value="{{ old('githubLink') }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="demoLink" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Demo URL</label>
                    <input type="url" id="demoLink" name="demoLink" value="{{ old('demoLink') }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Project Image</label>
                <input type="file" id="image" name="image" accept="image/*"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 hover:file:bg-indigo-100">
                <p class="text-xs text-gray-400 mt-1">Max 2MB. JPG, PNG, or WebP.</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 space-y-4">
            <h2 class="text-sm font-semibold border-b border-gray-200 dark:border-gray-700 pb-2">Technologies & Metrics</h2>

            <div>
                <label for="technologies" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Technologies <span class="text-red-500">*</span></label>
                <input type="text" id="technologies" name="technologies[]" value="{{ old('technologies.0') }}" required placeholder="e.g. React"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 mb-2">
                <input type="text" name="technologies[]" value="{{ old('technologies.1') }}" placeholder="e.g. Tailwind CSS"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 mb-2">
                <input type="text" name="technologies[]" value="{{ old('technologies.2') }}" placeholder="e.g. Node.js"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 mb-2">
                <input type="text" name="technologies[]" value="{{ old('technologies.3') }}" placeholder="e.g. PostgreSQL"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">Add each technology on a separate line.</p>
            </div>

            <div>
                <label for="metrics" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Metrics <span class="text-red-500">*</span></label>
                <input type="text" id="metrics" name="metrics[]" value="{{ old('metrics.0') }}" required placeholder="e.g. 2 role scopes (landlord / tenant)"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 mb-2">
                <input type="text" name="metrics[]" value="{{ old('metrics.1') }}" placeholder="e.g. Real-time sync via Supabase Realtime"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 mb-2">
                <input type="text" name="metrics[]" value="{{ old('metrics.2') }}" placeholder="e.g. Full CRUD across 3 entity types"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-400 mt-1">Key metrics or highlights for this project.</p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.projects.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">Create project</button>
        </div>
    </form>
</div>
@endsection
