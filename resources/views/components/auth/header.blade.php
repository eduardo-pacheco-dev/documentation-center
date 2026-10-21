@props(['title', 'subtitle'])

<div class="flex flex-col items-center text-center">
    <img
        src="{{ asset('images/auth/logo.jpg') }}"
        alt="AFL Engenharia"
        width="640"
        height="437"
        class="auth-logo h-20 w-auto object-contain mix-blend-multiply sm:h-24 md:h-28"
    />

    <h1 class="auth-reveal mt-5 text-sb24 text-grey-15 sm:mt-6 sm:text-sb32" style="--auth-delay: 120ms">{{ $title }}</h1>
    <p class="auth-reveal mt-2 text-[15px] text-grey-45" style="--auth-delay: 200ms">{{ $subtitle }}</p>
</div>
