<x-layouts.auth title="Entrar">
    <noscript>
        <style>
            [data-password-step][hidden] {
                display: block !important;
            }
        </style>
    </noscript>

    <x-auth.illustrations />

    <x-auth.container>
        <x-auth.header title="Entrar" subtitle="para continuar na sua conta" />

        @if (session('status'))
            <noscript>
                <div class="mt-7 rounded-s border border-brand-navy/20 bg-brand-navy/5 px-4 py-3 text-sm text-brand-navy">
                    {{ session('status') }}
                </div>
            </noscript>
        @endif

        <form method="POST" action="{{ route('login') }}" data-login-form class="mt-7 space-y-4 sm:mt-9 sm:space-y-5">
            @csrf

            <x-ui.field
                name="email"
                label="E-mail"
                type="email"
                :value="old('email')"
                placeholder="Digite seu e-mail"
                autocomplete="email"
                autofocus
                enterkeyhint="next"
                :delay="260"
            />

            <div data-password-step class="space-y-4 sm:space-y-5" hidden>
                <x-ui.field
                    name="password"
                    label="Senha"
                    type="password"
                    placeholder="Digite sua senha"
                    autocomplete="current-password"
                    enterkeyhint="go"
                    revealable
                />

                <x-ui.checkbox name="remember" label="Manter-me conectado" />
            </div>

            <x-ui.button data-login-submit :delay="500">Entrar</x-ui.button>
        </form>

        <x-auth.footer-link
            text="Não tem uma conta?"
            action="Cadastre-se"
            :href="route('register')"
            :delay="580"
        />
    </x-auth.container>
</x-layouts.auth>
