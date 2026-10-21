<x-layouts.auth title="Entrar">
    <noscript>
        <style>
            [data-password-step][hidden] {
                display: block !important;
            }
        </style>
    </noscript>

    <x-auth-illustrations />

    <div class="relative w-full max-w-[550px]">
        <div class="flex flex-col items-center text-center">
            <img
                src="{{ asset('images/auth/logo.jpg') }}"
                alt="AFL Engenharia"
                width="640"
                height="437"
                class="auth-logo h-20 w-auto object-contain mix-blend-multiply sm:h-24 md:h-28"
            />

            <h1 class="auth-reveal mt-5 text-sb24 text-grey-15 sm:mt-6 sm:text-sb32" style="--auth-delay: 120ms">Entrar</h1>
            <p class="auth-reveal mt-2 text-[15px] text-grey-45" style="--auth-delay: 200ms">para continuar na sua conta</p>
        </div>

        @if (session('status'))
            <noscript>
                <div class="mt-7 rounded-s border border-brand-navy/20 bg-brand-navy/5 px-4 py-3 text-sm text-brand-navy">
                    {{ session('status') }}
                </div>
            </noscript>
        @endif

        <form method="POST" action="{{ route('login') }}" data-login-form class="mt-7 space-y-4 sm:mt-9 sm:space-y-5">
            @csrf

            <div class="auth-reveal" style="--auth-delay: 260ms">
                <label for="email" class="mb-2 block text-sm font-semibold text-grey-15">E-mail</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    value="{{ old('email') }}"
                    placeholder="Digite seu e-mail"
                    required
                    autofocus
                    enterkeyhint="next"
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                    class="block w-full rounded-s border border-grey-80 bg-grey-100 py-3 pl-3 pr-2 text-base text-grey-15 shadow-[0_1px_2px_rgba(31,31,31,0.04)] outline-none transition placeholder:text-grey-65 focus:border-secondary-blue-400 focus:ring-4 focus:ring-secondary-blue-400/15 sm:text-[15px] @error('email') border-red-500 focus:border-red-500 focus:ring-red-500/15 @enderror"
                />
                @error('email')
                    <p id="email-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div data-password-step class="space-y-4 sm:space-y-5" hidden>
                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-grey-15">Senha</label>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            placeholder="Digite sua senha"
                            required
                            enterkeyhint="go"
                            @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                            class="block w-full rounded-s border border-grey-80 bg-grey-100 py-3 pl-3 pr-12 text-base text-grey-15 shadow-[0_1px_2px_rgba(31,31,31,0.04)] outline-none transition placeholder:text-grey-65 focus:border-secondary-blue-400 focus:ring-4 focus:ring-secondary-blue-400/15 sm:text-[15px] @error('password') border-red-500 focus:border-red-500 focus:ring-red-500/15 @enderror"
                        />
                        <button
                            type="button"
                            data-password-toggle="password"
                            aria-controls="password"
                            aria-pressed="false"
                            aria-label="Mostrar senha"
                            class="absolute inset-y-0 right-1 flex w-11 items-center justify-center rounded-s text-grey-45 transition hover:text-grey-15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-secondary-blue-400/40 active:scale-90"
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
                    Manter-me conectado
                </label>
            </div>

            <button
                type="submit"
                data-login-submit
                style="--auth-delay: 500ms"
                class="auth-reveal inline-flex w-full cursor-pointer items-center justify-center rounded-sm border border-transparent bg-secondary-blue-400 px-4 py-3 text-[15px] font-semibold text-white shadow-[0_8px_20px_rgba(31,115,232,0.24)] transition duration-200 hover:-translate-y-0.5 hover:bg-secondary-blue-500 hover:shadow-[0_12px_26px_rgba(31,115,232,0.3)] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-secondary-blue-400/25 active:translate-y-0 active:scale-[0.99] disabled:cursor-not-allowed disabled:border-grey-80 disabled:bg-grey-90 disabled:text-grey-65 disabled:shadow-none disabled:hover:translate-y-0"
            >
                Entrar
            </button>
        </form>

        <div class="auth-reveal mt-6 border-t border-grey-90 pt-6 text-center text-sm text-grey-45 sm:mt-8" style="--auth-delay: 580ms">
            <p>
                Não tem uma conta?
                <a href="{{ route('register') }}" class="font-semibold text-secondary-blue-400 transition hover:text-secondary-blue-500 hover:underline">Cadastre-se</a>
            </p>
        </div>
    </div>
</x-layouts.auth>
