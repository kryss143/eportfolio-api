<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login — {{ config('app.name', 'EPortfolio') }}</title>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/css/admin.css'])
    @endif
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center text-white font-bold text-xl mx-auto mb-4">EP</div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">EPortfolio Admin</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sign in to manage your portfolio</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm">
            @if ($errors->any())
                <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-sm">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="remember" class="ml-2 text-sm text-gray-600 dark:text-gray-400">Remember me</label>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Sign in
                </button>
            </form>
        </div>
    </div>
</body>
</html>
