@extends('admin.layouts.app')

@section('title', 'Edit: ' . $project->title)

@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Edit Project</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Updating: {{ $project->title }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.projects.update', $project) }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 space-y-4">
            <h2 class="text-sm font-semibold border-b border-gray-200 dark:border-gray-700 pb-2">Basic Information</h2>

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                <input type="text" id="title" name="title" value="{{ old('title', $project->title) }}" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea id="description" name="description" rows="3" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">{{ old('description', $project->description) }}</textarea>
            </div>

            <div>
                <label for="outcome" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Outcome</label>
                <textarea id="outcome" name="outcome" rows="2" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">{{ old('outcome', $project->outcome) }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select id="status" name="status" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                        <option value="built" {{ old('status', $project->status) === 'built' ? 'selected' : '' }}>Built</option>
                        <option value="in-progress" {{ old('status', $project->status) === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="featured" value="1" {{ old('featured', $project->featured) ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
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
                    <input type="url" id="githubLink" name="githubLink" value="{{ old('githubLink', $project->githubLink) }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="demoLink" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Demo URL</label>
                    <input type="url" id="demoLink" name="demoLink" value="{{ old('demoLink', $project->demoLink) }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            @if ($project->image)
                <div class="flex items-center gap-3">
                    <img src="{{ Storage::disk('public')->url($project->image) }}" alt="Current image" class="w-16 h-16 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                    <div>
                        <p class="text-sm font-medium">Current image</p>
                        <p class="text-xs text-gray-400">Upload a new one to replace it.</p>
                    </div>
                </div>
            @endif

            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $project->image ? 'Replace Image' : 'Project Image' }}</label>
                <input type="file" id="image" name="image" accept="image/*"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 hover:file:bg-indigo-100">
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 space-y-4">
            <h2 class="text-sm font-semibold border-b border-gray-200 dark:border-gray-700 pb-2">Technologies & Metrics</h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Technologies</label>
                @foreach (old('technologies', $project->technologies ?? []) as $i => $tech)
                    <input type="text" name="technologies[]" value="{{ $tech }}" {{ $i === 0 ? 'required' : '' }}
                        placeholder="Technology name"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 mb-2">
                @endforeach
                @if (count(old('technologies', $project->technologies ?? [])) === 0)
                    <input type="text" name="technologies[]" required placeholder="e.g. React"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 mb-2">
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Metrics</label>
                @foreach (old('metrics', $project->metrics ?? []) as $i => $metric)
                    <input type="text" name="metrics[]" value="{{ $metric }}" {{ $i === 0 ? 'required' : '' }}
                        placeholder="Key metric or highlight"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 mb-2">
                @endforeach
                @if (count(old('metrics', $project->metrics ?? [])) === 0)
                    <input type="text" name="metrics[]" required placeholder="e.g. Full CRUD across 3 entity types"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 mb-2">
                @endif
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.projects.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</a>
            <button type="button" onclick="if (confirm('Delete this project permanently?')) { document.getElementById('delete-form').submit(); }" class="px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">Delete</button>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Save changes</button>
        </div>
    </form>

    <form id="delete-form" method="POST" action="{{ route('admin.projects.destroy', $project) }}">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
