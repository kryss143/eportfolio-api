@extends('admin.layouts.app')
@section('title', 'Edit: ' . $blog->title)
@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Edit Blog Post</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Updating: {{ $blog->title }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
            <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.blogs.update', $blog) }}" class="space-y-5">
        @csrf
        @method('PUT')
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 space-y-4">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                <input type="text" id="title" name="title" value="{{ old('title', $blog->title) }}" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug</label>
                <input type="text" id="slug" name="slug" value="{{ old('slug', $blog->slug) }}" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Excerpt</label>
                <textarea id="excerpt" name="excerpt" rows="2" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">{{ old('excerpt', $blog->excerpt) }}</textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Publish Date</label>
                    <input type="date" id="date" name="date" value="{{ old('date', $blog->date ? \Carbon\Carbon::parse($blog->date)->format('Y-m-d') : '') }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="readTime" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Read Time</label>
                    <input type="text" id="readTime" name="readTime" value="{{ old('readTime', $blog->readTime) }}" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Content (HTML)</label>
                <textarea id="content" name="content" rows="15" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-indigo-500">{{ old('content', $blog->content) }}</textarea>
            </div>
        </div>
        <div class="flex items-center justify-between">
            <form method="POST" action="{{ route('admin.blogs.destroy', $blog) }}" onsubmit="return confirm('Delete this post?')">
                @csrf @method('DELETE')
                <button type="submit" class="px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">Delete</button>
            </form>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.blogs.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Save changes</button>
            </div>
        </div>
    </form>
</div>
@endsection
