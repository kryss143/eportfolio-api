@extends('admin.layouts.app')
@section('title', 'Create Blog Post')
@section('content')
<div class="max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Create Blog Post</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Write a new blog post for your portfolio.</p>
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

    <form method="POST" action="{{ route('admin.blogs.store') }}" class="space-y-5">
        @csrf
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 space-y-4">
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title <span class="text-red-500">*</span></label>
                <input type="text" id="title" name="title" value="{{ old('title') }}" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug <span class="text-red-500">*</span></label>
                <input type="text" id="slug" name="slug" value="{{ old('slug') }}" required placeholder="my-blog-post"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label for="excerpt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Excerpt <span class="text-red-500">*</span></label>
                <textarea id="excerpt" name="excerpt" rows="2" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">{{ old('excerpt') }}</textarea>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Publish Date</label>
                    <input type="date" id="date" name="date" value="{{ old('date') }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-400 mt-1">Leave empty for draft.</p>
                </div>
                <div>
                    <label for="readTime" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Read Time <span class="text-red-500">*</span></label>
                    <input type="text" id="readTime" name="readTime" value="{{ old('readTime', '5 min read') }}" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>
            <div>
                <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Content (HTML) <span class="text-red-500">*</span></label>
                <textarea id="content" name="content" rows="12" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-indigo-500">{{ old('content') }}</textarea>
                <p class="text-xs text-gray-400 mt-1">HTML content. Use &lt;p&gt; tags for paragraphs.</p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.blogs.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Create post</button>
        </div>
    </form>
</div>
@endsection
