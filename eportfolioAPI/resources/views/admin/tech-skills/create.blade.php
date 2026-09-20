@extends('admin.layouts.app')
@section('title', 'Add Tech Skill')
@section('content')
<div class="max-w-lg">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold">Add Tech Skill</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Add a technology to your skill set.</p>
    </div>

    @if ($errors->any())
        <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
            <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.tech-skills.store') }}" class="space-y-5">
        @csrf
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-5 space-y-4">
            <div>
                <label for="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Label <span class="text-red-500">*</span></label>
                <input type="text" id="label" name="label" value="{{ old('label') }}" required placeholder="e.g. React"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category <span class="text-red-500">*</span></label>
                <select id="category" name="category" required
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->value }}" {{ old('category') === $cat->value ? 'selected' : '' }}>{{ ucfirst($cat->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="logo" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Logo Path <span class="text-red-500">*</span></label>
                <input type="text" id="logo" name="logo" value="{{ old('logo') }}" required placeholder="./devicons/react-original.svg"
                    class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('admin.tech-skills.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg">Add skill</button>
        </div>
    </form>
</div>
@endsection
