<x-layouts.auth title="Sign in">
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 hidden overflow-hidden lg:block">
        <div class="absolute -left-28 top-16 h-80 w-80 rounded-full bg-brand-navy/10 blur-2xl"></div>
        <div class="absolute left-20 top-1/3 h-20 w-20 rotate-12 rounded-3xl border-2 border-brand-navy/20"></div>
        <svg class="absolute bottom-24 left-10 h-44 w-44 text-secondary-blue-400/20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="46" stroke="currentColor" stroke-width="1.5" stroke-dasharray="4 8" />
            <circle cx="50" cy="50" r="26" stroke="currentColor" stroke-width="1.5" />
            <path d="M50 18v64M18 50h64" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
        </svg>

        <div class="absolute -right-24 bottom-10 h-80 w-80 rounded-full bg-secondary-blue-400/10 blur-2xl"></div>
        <div class="absolute right-24 top-1/4 h-28 w-28 -rotate-12 rounded-full border-2 border-secondary-blue-400/20"></div>
        <svg class="absolute right-12 bottom-28 h-40 w-40 text-brand-navy/20" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 100 100">
            <path d="M12 78c18 0 20-52 40-52s18 26 36 26" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            <path d="M12 90c18 0 20-52 40-52s18 26 36 26" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-dasharray="3 7" />
        </svg>
    </div>

    <div class="relative w-full max-w-[550px]">
        <div class="flex flex-col items-center text-center">
            <img
                src="{{ asset('images/auth/logo.jpg') }}"
                alt="AFL Engenharia"
                width="640"
                height="437"
                class="h-24 w-auto object-contain mix-blend-multiply sm:h-28"
            />

            <h1 class="mt-6 text-sb24 text-grey-15 sm:text-sb32">Sign in</h1>
            <p class="mt-2 text-[15px] text-grey-45">to continue to your account</p>
        </div>

        @if (session('status'))
            <noscript>
                <div class="mt-7 rounded-s border border-brand-navy/20 bg-brand-navy/5 px-4 py-3 text-sm text-brand-navy">
                    {{ session('status') }}
                </div>
            </noscript>
        @endif

        <form method="POST" action="{{ route('login') }}" data-login-form class="mt-9 space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-2 block text-sm font-semibold text-grey-15">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    value="{{ old('email') }}"
                    placeholder="Enter your email"
                    required
                    autofocus
                    data-login-required
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                    class="block w-full rounded-s border border-grey-80 bg-grey-100 py-3 pl-3 pr-2 text-[15px] text-grey-15 shadow-[0_1px_2px_rgba(31,31,31,0.04)] outline-none transition placeholder:text-grey-65 focus:border-secondary-blue-400 focus:ring-4 focus:ring-secondary-blue-400/15 @error('email') border-red-500 focus:border-red-500 focus:ring-red-500/15 @enderror"
                />
                @error('email')
                    <p id="email-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="mb-2 block text-sm font-semibold text-grey-15">Password</label>
                <div class="relative">
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        required
                        data-login-required
                        @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                        class="block w-full rounded-s border border-grey-80 bg-grey-100 py-3 pl-3 pr-12 text-[15px] text-grey-15 shadow-[0_1px_2px_rgba(31,31,31,0.04)] outline-none transition placeholder:text-grey-65 focus:border-secondary-blue-400 focus:ring-4 focus:ring-secondary-blue-400/15 @error('password') border-red-500 focus:border-red-500 focus:ring-red-500/15 @enderror"
                    />
                    <button
                        type="button"
                        data-password-toggle="password"
                        aria-controls="password"
                        aria-pressed="false"
                        aria-label="Show password"
                        class="absolute inset-y-0 right-1 flex w-11 items-center justify-center rounded-s text-grey-45 transition hover:text-grey-15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary-blue-400/40"
                    >
                        <x-icon name="eye" data-password-icon="visible" class="h-5 w-5" />
                        <x-icon name="eye-slash" data-password-icon="hidden" class="hidden h-5 w-5" />
                    </button>
                </div>
                @error('password')
                    <p id="password-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex w-fit cursor-pointer items-center gap-2.5 text-sm text-grey-45">
                <input
                    type="checkbox"
                    name="remember"
                    value="1"
                    class="h-4 w-4 rounded border-grey-80 accent-secondary-blue-400 focus:ring-secondary-blue-400/30"
                />
                Keep me signed in
            </label>

            <button
                type="submit"
                data-login-submit
                class="inline-flex w-full cursor-pointer items-center justify-center rounded-sm border border-transparent bg-secondary-blue-400 px-4 py-3 text-[15px] font-semibold text-white shadow-[0_8px_20px_rgba(31,115,232,0.24)] transition duration-200 hover:bg-secondary-blue-500 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-secondary-blue-400/25 disabled:cursor-not-allowed disabled:border-grey-80 disabled:bg-grey-90 disabled:text-grey-65 disabled:shadow-none"
            >
                Continue
            </button>
        </form>

        <div class="mt-5 text-center">
            <a href="mailto:{{ config('mail.from.address') }}?subject={{ urlencode('Password reset request') }}" class="text-sm font-semibold text-secondary-blue-400 transition hover:text-secondary-blue-500 hover:underline">
                Forgot password?
            </a>
        </div>

        <div class="mt-8 border-t border-grey-90 pt-6 text-center text-sm text-grey-45">
            <p>
                Don’t have an account?
                <a href="{{ route('register') }}" class="font-semibold text-secondary-blue-400 transition hover:text-secondary-blue-500 hover:underline">Sign up</a>
            </p>
            <p class="mt-3">
                <a href="mailto:{{ config('mail.from.address') }}?subject={{ urlencode('Sign-in help') }}" class="transition hover:text-grey-15 hover:underline">
                    Can’t sign in? Click here
                </a>
            </p>
        </div>
    </div>
</x-layouts.auth>
