@extends('admin.layouts.app')
@section('title', 'Edit: ' . $techSkill->label)
@section('content')
<div class="max-w-lg">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Edit Tech Skill</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Updating: {{ $techSkill->label }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
            <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.tech-skills.update', $techSkill) }}" class="space-y-5">
        @csrf
        @method('PUT')
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 space-y-4">
            <div>
                <label for="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Label</label>
                <input type="text" id="label" name="label" value="{{ old('label', $techSkill->label) }}" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                <select id="category" name="category" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->value }}" {{ old('category', $techSkill->category?->value) === $cat->value ? 'selected' : '' }}>{{ ucfirst($cat->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Logo Path</label>
                <input type="text" id="logo" name="logo" value="{{ old('logo', $techSkill->logo) }}" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.tech-skills.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Save changes</button>
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.tech-skills.destroy', $techSkill) }}" onsubmit="return confirm('Delete this skill?')">
        @csrf @method('DELETE')
        <button type="submit" class="mt-4 w-full px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg">Delete this skill</button>
    </form>
</div>
@endsection
