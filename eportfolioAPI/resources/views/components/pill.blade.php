@props(['tone' => 'neutral'])

@php
$tones = [
    'success' => 'border-emerald-600/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
    'warning' => 'border-amber-600/30 bg-amber-500/10 text-amber-700 dark:text-amber-400',
    'danger'  => 'border-red-600/30 bg-red-500/10 text-red-700 dark:text-red-400',
    'info'    => 'border-sky-600/30 bg-sky-500/10 text-sky-700 dark:text-sky-400',
    'neutral' => 'border-gray-300/80 bg-gray-500/10 text-gray-600 dark:border-gray-600 dark:text-gray-400',
];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-xs font-medium '.$tones[$tone]]) }}>
    <span class="size-1.5 rounded-full bg-current" aria-hidden="true"></span>{{ $slot }}
</span>
