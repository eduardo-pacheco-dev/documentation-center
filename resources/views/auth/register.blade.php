<x-layouts.auth title="Criar conta">
    <x-auth.illustrations />

    <x-auth.container>
        <x-auth.header title="Criar conta" subtitle="para acessar o painel administrativo" />

        <form method="POST" action="{{ route('register') }}" class="mt-7 space-y-4 sm:mt-9 sm:space-y-5">
            @csrf

            <x-ui.field
                name="name"
                label="Nome completo"
                :value="old('name')"
                placeholder="Digite seu nome completo"
                autocomplete="name"
                autofocus
                enterkeyhint="next"
                :delay="260"
            />

            <x-ui.field
                name="email"
                label="E-mail"
                type="email"
                :value="old('email')"
                placeholder="Digite seu e-mail"
                autocomplete="email"
                enterkeyhint="next"
                :delay="340"
            />

            <x-ui.field
                name="password"
                label="Senha"
                type="password"
                placeholder="Crie uma senha"
                autocomplete="new-password"
                enterkeyhint="next"
                revealable
                :delay="420"
            />

            <x-ui.field
                name="password_confirmation"
                label="Confirmar senha"
                type="password"
                placeholder="Repita a senha"
                autocomplete="new-password"
                enterkeyhint="done"
                revealable
                :delay="500"
            />

            <x-ui.button :delay="580">Criar conta</x-ui.button>
        </form>

        <x-auth.footer-link
            text="Já tem uma conta?"
            action="Entrar"
            :href="route('login')"
            :delay="660"
        />
    </x-auth.container>
</x-layouts.auth>
