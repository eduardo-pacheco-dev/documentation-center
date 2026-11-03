@props(['text', 'action', 'href', 'delay' => 580])

<div class="auth-reveal mt-6 border-t border-grey-90 pt-6 text-center text-sm text-grey-45 sm:mt-8 dark:border-white/10 dark:text-gray-400" style="--auth-delay: {{ $delay }}ms">
    <p>
        {{ $text }}
        <a href="{{ $href }}" class="font-semibold text-secondary-blue-400 transition hover:text-secondary-blue-500 hover:underline dark:text-blue-400 dark:hover:text-blue-300">{{ $action }}</a>
    </p>
</div>
