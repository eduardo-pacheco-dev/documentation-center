<x-layouts.auth title="Entrar">
    <div aria-hidden="true" class="pointer-events-none absolute inset-0 hidden overflow-hidden lg:block">
        <div class="absolute -left-28 top-16 h-80 w-80 rounded-full bg-brand-navy/10 blur-2xl"></div>
        <div class="absolute left-20 top-1/3 h-20 w-20 rotate-12 rounded-3xl border-2 border-brand-navy/20"></div>

        <svg class="absolute left-2 top-1/2 h-52 w-52 -translate-y-1/2 text-brand-navy/25 lg:left-4 xl:left-[8%] xl:h-72 xl:w-72" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 200 240" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M72 36a40 40 0 0 1 56 0" opacity="0.45" />
            <path d="M84 48a24 24 0 0 1 32 0" opacity="0.7" />
            <path d="M93 60a10 10 0 0 1 14 0" />
            <path d="M100 20v20" />
            <circle cx="100" cy="15" r="2.5" />
            <path d="M100 40 56 212M100 40l44 172" />
            <path d="M87 90h26M78 124h44M70 158h60M61 192h78" />
            <path d="M87 90l35 34M113 90l-35 34M78 124l52 34M122 124l-52 34M70 158l69 34M130 158l-69 34" />
            <path d="M40 212h120" />
            <path d="M30 70h10M35 65v10" opacity="0.35" />
        </svg>

        <div class="absolute -right-24 bottom-10 h-80 w-80 rounded-full bg-secondary-blue-400/10 blur-2xl"></div>
        <div class="absolute right-24 top-1/4 h-28 w-28 -rotate-12 rounded-full border-2 border-secondary-blue-400/20"></div>

        <svg class="absolute right-2 top-1/2 h-52 w-52 -translate-y-1/2 text-secondary-blue-400/30 lg:right-4 xl:right-[8%] xl:h-72 xl:w-72" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 240 220" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <g transform="rotate(-14 120 112)">
                <ellipse cx="120" cy="112" rx="106" ry="64" stroke-dasharray="3 8" opacity="0.5" />
                <rect x="103" y="88" width="34" height="48" rx="6" />
                <path d="M103 100h34M103 124h34" opacity="0.6" />
                <rect x="34" y="96" width="58" height="32" rx="2" />
                <path d="M48 96v32M63 96v32M78 96v32M34 112h58" opacity="0.6" />
                <rect x="148" y="96" width="58" height="32" rx="2" />
                <path d="M162 96v32M177 96v32M192 96v32M148 112h58" opacity="0.6" />
                <path d="M92 112h11M137 112h11" />
                <path d="M108 88V76a12 12 0 0 1 24 0v12" />
                <path d="M120 58v6" />
                <path d="M136 62a22 22 0 0 1 0 20" opacity="0.6" />
                <path d="M146 54a36 36 0 0 1 0 36" opacity="0.4" />
                <path d="M120 136v12" />
                <circle cx="120" cy="152" r="2.5" />
            </g>
            <path d="M24 40h10M29 35v10" opacity="0.35" />
            <path d="M206 178h10M211 173v10" opacity="0.35" />
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

            <h1 class="mt-6 text-sb24 text-grey-15 sm:text-sb32">Entrar</h1>
            <p class="mt-2 text-[15px] text-grey-45">para continuar na sua conta</p>
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
                    data-login-required
                    @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                    class="block w-full rounded-s border border-grey-80 bg-grey-100 py-3 pl-3 pr-2 text-[15px] text-grey-15 shadow-[0_1px_2px_rgba(31,31,31,0.04)] outline-none transition placeholder:text-grey-65 focus:border-secondary-blue-400 focus:ring-4 focus:ring-secondary-blue-400/15 @error('email') border-red-500 focus:border-red-500 focus:ring-red-500/15 @enderror"
                />
                @error('email')
                    <p id="email-error" class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

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
                        data-login-required
                        @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                        class="block w-full rounded-s border border-grey-80 bg-grey-100 py-3 pl-3 pr-12 text-[15px] text-grey-15 shadow-[0_1px_2px_rgba(31,31,31,0.04)] outline-none transition placeholder:text-grey-65 focus:border-secondary-blue-400 focus:ring-4 focus:ring-secondary-blue-400/15 @error('password') border-red-500 focus:border-red-500 focus:ring-red-500/15 @enderror"
                    />
                    <button
                        type="button"
                        data-password-toggle="password"
                        aria-controls="password"
                        aria-pressed="false"
                        aria-label="Mostrar senha"
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
                Manter-me conectado
            </label>

            <button
                type="submit"
                data-login-submit
                class="inline-flex w-full cursor-pointer items-center justify-center rounded-sm border border-transparent bg-secondary-blue-400 px-4 py-3 text-[15px] font-semibold text-white shadow-[0_8px_20px_rgba(31,115,232,0.24)] transition duration-200 hover:bg-secondary-blue-500 focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-secondary-blue-400/25 disabled:cursor-not-allowed disabled:border-grey-80 disabled:bg-grey-90 disabled:text-grey-65 disabled:shadow-none"
            >
                Continuar
            </button>
        </form>

        <div class="mt-8 border-t border-grey-90 pt-6 text-center text-sm text-grey-45">
            <p>
                Não tem uma conta?
                <a href="{{ route('register') }}" class="font-semibold text-secondary-blue-400 transition hover:text-secondary-blue-500 hover:underline">Cadastre-se</a>
            </p>
        </div>
    </div>
</x-layouts.auth>
