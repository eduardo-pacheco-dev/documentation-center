<x-layouts.admin title="Configurações do Follow-up">
    @php
        $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
        $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
    @endphp

    <x-ui.page-header
        category="Acompanhamento"
        title="Configurações do Follow-up"
        description="Ajuste a listagem, os alertas de prazo e o envio de e-mails do plugin."
        icon="cog-6-tooth"
    >
        <x-ui.action :href="route('admin.follow-ups.index')" icon="chevron-left" variant="secondary">
            Voltar
        </x-ui.action>
    </x-ui.page-header>

    <div x-data="{ testEmailOpen: false }">
    <form method="POST" action="{{ route('admin.follow-ups.settings.update') }}" class="mt-6 space-y-4">
        @csrf
        @method('PUT')

        <section class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="table-cells" class="h-5 w-5" />
                </span>

                <div>
                    <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Listagem e prazos</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Controlam a tabela de follow-ups, os alertas de prazo e o widget do dashboard.</p>
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="per_page" class="{{ $labelClass }}">Itens por página</label>
                    <input
                        id="per_page"
                        name="per_page"
                        type="number"
                        min="5"
                        max="100"
                        value="{{ old('per_page', $settings->per_page) }}"
                        required
                        class="{{ $inputClass }}"
                    >
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Entre 5 e 100.</p>
                    @error('per_page')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="due_soon_days" class="{{ $labelClass }}">Alerta de prazo (dias)</label>
                    <input
                        id="due_soon_days"
                        name="due_soon_days"
                        type="number"
                        min="1"
                        max="60"
                        value="{{ old('due_soon_days', $settings->due_soon_days) }}"
                        required
                        class="{{ $inputClass }}"
                    >
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Destaca prazos próximos do vencimento.</p>
                    @error('due_soon_days')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="widget_limit" class="{{ $labelClass }}">Itens no widget</label>
                    <input
                        id="widget_limit"
                        name="widget_limit"
                        type="number"
                        min="1"
                        max="20"
                        value="{{ old('widget_limit', $settings->widget_limit) }}"
                        required
                        class="{{ $inputClass }}"
                    >
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Follow-ups exibidos no dashboard.</p>
                    @error('widget_limit')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        <section
            class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]"
            x-data="{ smtp: @js((bool) old('mail_enabled', $settings->mail_enabled)) }"
        >
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="server" class="h-5 w-5" />
                </span>

                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Servidor de e-mail (SMTP)</h2>
                        @if (filled($settings->mail_password))
                            <x-ui.badge tone="green">Senha configurada</x-ui.badge>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Quando ativado, estas credenciais substituem a configuração de e-mail do sistema.</p>
                </div>
            </div>

            <label class="mt-5 flex items-center gap-2">
                <input type="hidden" name="mail_enabled" value="0">
                <input
                    type="checkbox"
                    name="mail_enabled"
                    value="1"
                    @checked(old('mail_enabled', $settings->mail_enabled))
                    x-model="smtp"
                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500/30 dark:border-white/20 dark:bg-white/[0.03] dark:text-indigo-400"
                >
                <span class="text-sm text-gray-700 dark:text-gray-200">Enviar e-mails usando o servidor SMTP abaixo</span>
            </label>
            @error('mail_enabled')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror

            <div x-show="smtp" x-cloak class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="mail_host" class="{{ $labelClass }}">Servidor (host)</label>
                    <input
                        id="mail_host"
                        name="mail_host"
                        type="text"
                        value="{{ old('mail_host', $settings->mail_host) }}"
                        placeholder="smtp.exemplo.com"
                        class="{{ $inputClass }}"
                    >
                    @error('mail_host')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="mail_port" class="{{ $labelClass }}">Porta</label>
                        <input
                            id="mail_port"
                            name="mail_port"
                            type="number"
                            min="1"
                            max="65535"
                            value="{{ old('mail_port', $settings->mail_port ?? 587) }}"
                            class="{{ $inputClass }}"
                        >
                        @error('mail_port')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="mail_encryption" class="{{ $labelClass }}">Criptografia</label>
                        <select id="mail_encryption" name="mail_encryption" class="{{ $inputClass }}">
                            <option value="" @selected(blank(old('mail_encryption', $settings->mail_encryption)))>Nenhuma</option>
                            <option value="tls" @selected(old('mail_encryption', $settings->mail_encryption) === 'tls')>TLS</option>
                            <option value="ssl" @selected(old('mail_encryption', $settings->mail_encryption) === 'ssl')>SSL</option>
                        </select>
                        @error('mail_encryption')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="mail_username" class="{{ $labelClass }}">Usuário</label>
                    <input
                        id="mail_username"
                        name="mail_username"
                        type="text"
                        autocomplete="off"
                        value="{{ old('mail_username', $settings->mail_username) }}"
                        class="{{ $inputClass }}"
                    >
                    @error('mail_username')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="mail_password" class="{{ $labelClass }}">Senha</label>
                    <input
                        id="mail_password"
                        name="mail_password"
                        type="password"
                        autocomplete="new-password"
                        placeholder="{{ filled($settings->mail_password) ? 'Deixe em branco para manter' : '' }}"
                        class="{{ $inputClass }}"
                    >
                    @error('mail_password')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="mail_from_address" class="{{ $labelClass }}">E-mail do remetente</label>
                    <input
                        id="mail_from_address"
                        name="mail_from_address"
                        type="email"
                        value="{{ old('mail_from_address', $settings->mail_from_address ?? config('mail.from.address')) }}"
                        placeholder="nao-responda@exemplo.com"
                        class="{{ $inputClass }}"
                    >
                    @error('mail_from_address')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="mail_from_name" class="{{ $labelClass }}">Nome do remetente</label>
                    <input
                        id="mail_from_name"
                        name="mail_from_name"
                        type="text"
                        value="{{ old('mail_from_name', $settings->mail_from_name ?? config('mail.from.name')) }}"
                        placeholder="{{ config('app.name') }}"
                        class="{{ $inputClass }}"
                    >
                    @error('mail_from_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-white/10">
                <x-ui.action type="button" variant="secondary" icon="paper-airplane" x-on:click="testEmailOpen = true">
                    Enviar e-mail de teste
                </x-ui.action>

                <p class="text-xs text-gray-500 dark:text-gray-400">Usa as configurações de e-mail atualmente salvas.</p>
            </div>
        </section>

<div class="flex flex-wrap items-center gap-2">
            <x-ui.action type="submit" icon="check">Salvar configurações</x-ui.action>
            <x-ui.action :href="route('admin.follow-ups.index')" variant="secondary">Cancelar</x-ui.action>
        </div>
    </form>

    <div
        x-data="{ assigneeOpen: @js($errors->hasAny(['name', 'email'])) }"
        x-on:keydown.escape.window="assigneeOpen = false"
    >
    <section class="mt-4 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="users" class="h-5 w-5" />
                </span>

                <div>
                    <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Responsáveis</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre as pessoas que podem ser atribuídas aos follow-ups. Esta lista não depende dos usuários do sistema.</p>
                </div>
            </div>

            <x-ui.action type="button" icon="plus" x-on:click="assigneeOpen = true">
                Adicionar responsável
            </x-ui.action>
        </div>

        <ul class="mt-5 divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/80 dark:divide-white/10 dark:border-white/10">
            @forelse ($assignees as $assignee)
                <li class="flex items-center justify-between gap-3 px-4 py-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $assignee->name }}</p>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $assignee->email ?: 'Sem e-mail' }}
                            <span aria-hidden="true">·</span>
                            {{ $assignee->follow_ups_count }} follow-up(s)
                        </p>
                    </div>

                    <form method="POST" action="{{ route('admin.follow-ups.settings.assignees.destroy', $assignee) }}" data-confirm-delete="Remover o responsável {{ $assignee->name }}? Os follow-ups atribuídos a ele ficarão sem responsável.">
                        @csrf
                        @method('DELETE')

                        <button
                            type="submit"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-red-500 transition hover:bg-red-50 hover:text-red-600 dark:text-red-400 dark:hover:bg-red-500/15"
                            aria-label="Remover {{ $assignee->name }}"
                        >
                            <x-icon name="trash" class="h-4 w-4" />
                        </button>
                    </form>
                </li>
            @empty
                <li class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                    Nenhum responsável cadastrado. Use "Adicionar responsável" para começar.
                </li>
            @endforelse
        </ul>
    </section>

    <div
        x-cloak
        x-show="assigneeOpen"
        x-transition.opacity.duration.100ms
        class="fixed inset-0 z-[100]"
        role="dialog"
        aria-modal="true"
        aria-labelledby="assignee-create-title"
    >
        <div class="absolute inset-0 bg-gray-900/50" x-on:click="assigneeOpen = false"></div>

        <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-5 shadow-xl dark:bg-[#1c1c1b]">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 id="assignee-create-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Novo responsável</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre quem pode ser atribuído aos follow-ups.</p>
                </div>

                <button
                    type="button"
                    x-on:click="assigneeOpen = false"
                    class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10"
                    aria-label="Fechar"
                >
                    <x-icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>

            <form method="POST" action="{{ route('admin.follow-ups.settings.assignees.store') }}" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label for="assignee_name" class="{{ $labelClass }}">Nome <span class="text-red-500">*</span></label>
                    <input
                        id="assignee_name"
                        name="name"
                        type="text"
                        maxlength="150"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        class="{{ $inputClass }}"
                    >
                    @error('name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="assignee_email" class="{{ $labelClass }}">E-mail <span class="font-normal text-gray-400">(opcional)</span></label>
                    <input
                        id="assignee_email"
                        name="email"
                        type="email"
                        maxlength="255"
                        value="{{ old('email') }}"
                        class="{{ $inputClass }}"
                    >
                    @error('email')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-white/10">
                    <x-ui.action type="submit" icon="plus">Adicionar</x-ui.action>
                    <x-ui.action type="button" variant="secondary" x-on:click="assigneeOpen = false">Cancelar</x-ui.action>
                </div>
            </form>
        </div>
    </div>
    </div>

        <section
        x-data="{ clientModal: @js($errors->hasAny(['client_id', 'client_name']) && old('source') !== 'client') }"
        x-on:keydown.escape.window="clientModal = false"
        class="mt-4 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]"
    >
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="building-office" class="h-5 w-5" />
                </span>

                <div>
                    <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Clientes e projetos</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre os clientes e os projetos de cada cliente usados nos follow-ups.</p>
                </div>
            </div>

            <x-ui.action type="button" icon="plus" x-on:click="clientModal = true">Adicionar cliente</x-ui.action>
        </div>

        <div class="mt-5 space-y-3">
            @forelse ($clients as $client)
                @php
                    $initials = collect(preg_split('/\s+/', trim($client->name)))
                        ->filter()
                        ->take(2)
                        ->map(fn ($part): string => mb_strtoupper(mb_substr($part, 0, 1)))
                        ->implode('');
                @endphp

                <div
                    x-data="{ editing: false, projectModal: @js($errors->hasAny(['project_name']) && old('source') === 'client' && (string) old('client_id') === (string) $client->getKey()) }"
                    class="rounded-2xl border border-gray-200/80 bg-gray-50/40 p-4 transition hover:border-indigo-200 hover:shadow-sm hover:shadow-indigo-600/5 dark:border-white/10 dark:bg-white/[0.02] dark:hover:border-indigo-500/40"
                >
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex min-w-0 items-start gap-3">
                            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-sm font-semibold text-white shadow-sm dark:bg-indigo-500">
                                {{ $initials ?: 'C' }}
                            </span>

                            <div class="min-w-0 flex-1">
                                <div x-show="! editing">
                                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $client->name }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="inline-flex items-center gap-1">
                                            <x-icon name="rectangle-stack" class="h-3.5 w-3.5" />
                                            {{ $client->projects->count() }} {{ $client->projects->count() === 1 ? 'projeto' : 'projetos' }}
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <x-icon name="clipboard-document-list" class="h-3.5 w-3.5" />
                                            {{ $client->follow_ups_count }} follow-up(s)
                                        </span>
                                    </div>
                                </div>

                                <form
                                    x-cloak
                                    x-show="editing"
                                    method="POST"
                                    action="{{ route('admin.follow-ups.settings.clients.update', $client) }}"
                                    class="flex flex-wrap items-center gap-2"
                                >
                                    @csrf
                                    @method('PUT')

                                    <input
                                        name="client_name"
                                        type="text"
                                        maxlength="150"
                                        value="{{ $client->name }}"
                                        required
                                        aria-label="Nome do cliente {{ $client->name }}"
                                        class="h-9 min-w-0 flex-1 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                    >

                                    <button type="submit" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-indigo-600 px-3 text-xs font-semibold text-white transition hover:bg-indigo-500">
                                        <x-icon name="check" class="h-3.5 w-3.5" />
                                        Salvar
                                    </button>

                                    <button type="button" x-on:click="editing = false" class="inline-flex h-9 items-center rounded-lg px-3 text-xs font-semibold text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-gray-200">
                                        Cancelar
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-1">
                            <button
                                type="button"
                                x-on:click="editing = ! editing"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-white hover:text-indigo-600 hover:shadow-sm dark:text-gray-500 dark:hover:bg-white/10 dark:hover:text-indigo-300"
                                aria-label="Editar cliente {{ $client->name }}"
                            >
                                <x-icon name="pencil-square" class="h-4 w-4" />
                            </button>

                            <form method="POST" action="{{ route('admin.follow-ups.settings.clients.destroy', $client) }}" data-confirm-delete="Remover o cliente {{ $client->name }}? Os projetos e vínculos dos follow-ups serão removidos.">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:text-gray-500 dark:hover:bg-red-500/10 dark:hover:text-red-400" aria-label="Remover cliente {{ $client->name }}">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-3 rounded-xl border border-gray-200/70 bg-white/80 p-2 dark:border-white/10 dark:bg-white/[0.03]">
                        <div class="flex items-center justify-between gap-2 px-1 py-1">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Projetos</p>
                            <div class="flex items-center gap-1">
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-500 dark:bg-white/10 dark:text-gray-400">{{ $client->projects->count() }}</span>
                                <button
                                    type="button"
                                    x-on:click="projectModal = true"
                                    class="inline-flex h-6 w-6 items-center justify-center rounded-md text-gray-400 transition hover:bg-indigo-50 hover:text-indigo-600 dark:text-gray-500 dark:hover:bg-indigo-500/10 dark:hover:text-indigo-300"
                                    aria-label="Adicionar projeto ao cliente {{ $client->name }}"
                                    title="Adicionar projeto"
                                >
                                    <x-icon name="plus" class="h-3.5 w-3.5" />
                                </button>
                            </div>
                        </div>

                        <div class="mt-1 space-y-1">
                            @forelse ($client->projects as $project)
                                <div x-data="{ editing: false }" class="rounded-lg transition hover:bg-gray-50 dark:hover:bg-white/[0.04]">
                                    <div x-show="! editing" class="flex items-center gap-2 px-2 py-2">
                                        <span class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400">
                                            <x-icon name="rectangle-stack" class="h-3.5 w-3.5" />
                                        </span>

                                        <span class="min-w-0 flex-1 truncate text-sm text-gray-700 dark:text-gray-200">{{ $project->name }}</span>

                                        <span class="shrink-0 text-[11px] text-gray-400 dark:text-gray-500">{{ $project->follow_ups_count }} follow-up(s)</span>

                                        <button
                                            type="button"
                                            x-on:click="editing = true"
                                            class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-gray-400 transition hover:bg-white hover:text-indigo-600 dark:text-gray-500 dark:hover:bg-white/10 dark:hover:text-indigo-300"
                                            aria-label="Editar projeto {{ $project->name }}"
                                        >
                                            <x-icon name="pencil-square" class="h-3.5 w-3.5" />
                                        </button>

                                        <form method="POST" action="{{ route('admin.follow-ups.settings.projects.destroy', $project) }}" data-confirm-delete="Remover o projeto {{ $project->name }}?">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="inline-flex h-7 w-7 items-center justify-center rounded-md text-gray-400 transition hover:bg-red-50 hover:text-red-600 dark:text-gray-500 dark:hover:bg-red-500/10 dark:hover:text-red-400" aria-label="Remover projeto {{ $project->name }}">
                                                <x-icon name="trash" class="h-3.5 w-3.5" />
                                            </button>
                                        </form>
                                    </div>

                                    <form
                                        x-cloak
                                        x-show="editing"
                                        method="POST"
                                        action="{{ route('admin.follow-ups.settings.projects.update', $project) }}"
                                        class="grid gap-2 p-2 sm:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)_auto]"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <select name="client_id" aria-label="Cliente do projeto {{ $project->name }}" class="h-9 rounded-lg border border-gray-300 bg-white px-2 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
                                            @foreach ($clients as $clientOption)
                                                <option value="{{ $clientOption->getKey() }}" @selected($project->client_id === $clientOption->getKey())>{{ $clientOption->name }}</option>
                                            @endforeach
                                        </select>

                                        <input
                                            name="project_name"
                                            type="text"
                                            maxlength="150"
                                            value="{{ $project->name }}"
                                            required
                                            aria-label="Nome do projeto {{ $project->name }}"
                                            class="h-9 min-w-0 rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                        >

                                        <div class="flex items-center gap-2">
                                            <button type="submit" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-indigo-600 px-3 text-xs font-semibold text-white transition hover:bg-indigo-500">
                                                <x-icon name="check" class="h-3.5 w-3.5" />
                                                Salvar
                                            </button>

                                            <button type="button" x-on:click="editing = false" class="inline-flex h-9 items-center rounded-lg px-2 text-xs font-semibold text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-gray-200">
                                                Cancelar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @empty
                                <p class="px-2 py-2 text-xs text-gray-500 dark:text-gray-400">Nenhum projeto cadastrado para este cliente.</p>
                            @endforelse
                        </div>
                    </div>

                    <div
                        x-cloak
                        x-show="projectModal"
                        x-transition.opacity.duration.100ms
                        class="fixed inset-0 z-[100]"
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="project-form-title-{{ $client->getKey() }}"
                    >
                        <div class="absolute inset-0 bg-gray-900/50" x-on:click="projectModal = false"></div>

                        <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-5 shadow-xl dark:bg-[#1c1c1b]">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h2 id="project-form-title-{{ $client->getKey() }}" class="text-base font-semibold text-gray-900 dark:text-gray-100">Novo projeto</h2>
                                    <p class="mt-1 truncate text-sm text-gray-500 dark:text-gray-400">{{ $client->name }}</p>
                                </div>

                                <button
                                    type="button"
                                    x-on:click="projectModal = false"
                                    class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10"
                                    aria-label="Fechar"
                                >
                                    <x-icon name="x-mark" class="h-4 w-4" />
                                </button>
                            </div>

                            <form method="POST" action="{{ route('admin.follow-ups.settings.clients.store') }}" class="mt-4 space-y-4">
                                @csrf
                                @method('POST')

                                <input type="hidden" name="source" value="client">
                                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">

                                <div>
                                    <label for="project_name_{{ $client->getKey() }}" class="{{ $labelClass }}">Nome do projeto <span class="text-red-500">*</span></label>
                                    <input
                                        id="project_name_{{ $client->getKey() }}"
                                        name="project_name"
                                        type="text"
                                        maxlength="150"
                                        value="{{ old('source') === 'client' && (string) old('client_id') === (string) $client->getKey() ? old('project_name') : '' }}"
                                        placeholder="Ex.: Expansão 5G"
                                        required
                                        class="{{ $inputClass }}"
                                    >
                                    @if (old('source') === 'client' && (string) old('client_id') === (string) $client->getKey())
                                        @error('project_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                                    @endif
                                </div>

                                <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-white/10">
                                    <x-ui.action type="submit" icon="plus">Adicionar</x-ui.action>
                                    <x-ui.action type="button" variant="secondary" x-on:click="projectModal = false">Cancelar</x-ui.action>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 px-4 py-10 text-center dark:border-white/15">
                    <span class="mx-auto inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gray-100 text-gray-400 dark:bg-white/10 dark:text-gray-500">
                        <x-icon name="building-office" class="h-5 w-5" />
                    </span>
                    <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-200">Nenhum cliente cadastrado</p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use o botão Adicionar para cadastrar o primeiro cliente.</p>
                </div>
            @endforelse
        </div>
<div
            x-cloak
            x-show="clientModal"
            x-transition.opacity.duration.100ms
            class="fixed inset-0 z-[100]"
            role="dialog"
            aria-modal="true"
            aria-labelledby="client-form-title"
        >
            <div class="absolute inset-0 bg-gray-900/50" x-on:click="clientModal = false"></div>

            <div class="absolute left-1/2 top-1/2 max-h-[90vh] w-full max-w-xl -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-2xl bg-white p-5 shadow-xl dark:bg-[#1c1c1b]">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h2 id="client-form-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Novo cliente</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Selecione um cliente existente ou digite um nome novo para cadastrá-lo.</p>
                    </div>

                    <button
                        type="button"
                        x-on:click="clientModal = false"
                        class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10"
                        aria-label="Fechar"
                    >
                        <x-icon name="x-mark" class="h-4 w-4" />
                    </button>
                </div>

@php
            $selectedClientId = (string) old('client_id', '');
            $selectedClientName = (string) old('client_name', '');
            $selectedClient = $selectedClientId !== '' && ctype_digit($selectedClientId)
                ? $clients->firstWhere('id', (int) $selectedClientId)
                : null;
            $clientSearch = $selectedClient?->name ?? $selectedClientName;
            $clientOptions = $clients->map(fn ($client): array => [
                'id' => (string) $client->getKey(),
                'name' => $client->name,
            ])->values()->all();
        @endphp

        <form
            method="POST"
            action="{{ route('admin.follow-ups.settings.clients.store') }}"
            class="mt-4"
            x-data="{
                open: false,
                search: @js($clientSearch),
                clientId: @js((string) $selectedClientId),
                clientName: @js($selectedClientId === '' ? $selectedClientName : ''),
                clients: @js($clientOptions),
                active: 0,
                get filtered() {
                    const term = this.search.trim().toLowerCase();

                    if (term === '') {
                        return this.clients;
                    }

                    return this.clients.filter((client) => client.name.toLowerCase().includes(term));
                },
                get exact() {
                    return this.clients.find((client) => client.name.toLowerCase() === this.search.trim().toLowerCase());
                },
                onInput() {
                    this.clientId = '';
                    this.clientName = this.search;
                    this.open = true;
                    this.active = 0;
                },
                choose(client) {
                    this.clientId = client.id;
                    this.clientName = '';
                    this.search = client.name;
                    this.open = false;
                },
                create() {
                    this.clientId = '';
                    this.clientName = this.search.trim();
                    this.open = false;
                },
                clear() {
                    this.search = '';
                    this.clientId = '';
                    this.clientName = '';
                    this.open = true;
                    this.$nextTick(() => this.$refs.clientInput.focus());
                },
                move(step) {
                    const max = this.filtered.length - 1;
                    this.active = Math.min(Math.max(this.active + step, 0), Math.max(max, 0));
                },
                chooseActive() {
                    if (this.filtered[this.active]) {
                        this.choose(this.filtered[this.active]);

                        return;
                    }

                    if (this.search.trim() !== '') {
                        this.create();
                    }
                },
            }"
            @click.outside="open = false"
            @keydown.escape.stop="open = false"
        >
            @csrf

            <input type="hidden" name="source" value="top">
            <input type="hidden" name="client_id" :value="clientId">
            <input type="hidden" name="client_name" :value="clientName">

            <div class="grid gap-3">
                <div>
                    <label for="client_search" class="{{ $labelClass }}">Cliente</label>

                    <div class="relative">
                        <input
                            id="client_search"
                            x-ref="clientInput"
                            type="text"
                            x-model="search"
                            @focus="open = true"
                            @input="onInput()"
                            @keydown.arrow-down.prevent="open = true; move(1)"
                            @keydown.arrow-up.prevent="move(-1)"
                            @keydown.enter.prevent="chooseActive()"
                            placeholder="Busque ou digite um novo cliente"
                            autocomplete="off"
                            role="combobox"
                            :aria-expanded="open"
                            class="{{ $inputClass }} pr-16"
                        >

                        <button
                            type="button"
                            x-show="search !== ''"
                            x-cloak
                            @click="clear()"
                            class="absolute right-9 top-1/2 -translate-y-1/2 rounded-full p-0.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                            aria-label="Limpar cliente"
                        >
                            <x-icon name="x-mark" class="h-3.5 w-3.5" />
                        </button>

                        <button
                            type="button"
                            @click="open = ! open"
                            class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                            aria-label="Mostrar clientes"
                        >
                            <x-icon name="chevron-up-down" class="h-4 w-4" />
                        </button>

                        <div
                            x-cloak
                            x-show="open"
                            x-transition.origin.top
                            class="absolute left-0 right-0 z-30 mt-1 max-h-56 overflow-auto rounded-xl border border-gray-200 bg-white p-1 shadow-lg dark:border-white/10 dark:bg-[#1c1c1b]"
                            role="listbox"
                        >
                            <template x-for="(client, index) in filtered" :key="client.id">
                                <button
                                    type="button"
                                    @mousedown.prevent="choose(client)"
                                    @mouseenter="active = index"
                                    :class="active === index ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-200' : 'text-gray-700 dark:text-gray-200'"
                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm transition"
                                    role="option"
                                    :aria-selected="String(client.id) === String(clientId)"
                                >
                                    <x-icon name="building-office" class="h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />
                                    <span class="min-w-0 flex-1 truncate" x-text="client.name"></span>
                                    <x-icon x-show="String(client.id) === String(clientId)" name="check" class="h-4 w-4 shrink-0 text-indigo-600 dark:text-indigo-400" />
                                </button>
                            </template>

                            <button
                                type="button"
                                x-show="search.trim() !== '' && ! exact"
                                @mousedown.prevent="create()"
                                class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm font-medium text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-300 dark:hover:bg-indigo-500/10"
                            >
                                <x-icon name="plus" class="h-4 w-4 shrink-0" />
                                <span class="min-w-0 truncate">Criar cliente "<span x-text="search.trim()"></span>"</span>
                            </button>

                            <p x-show="filtered.length === 0 && search.trim() === ''" class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400">
                                Nenhum cliente cadastrado. Digite um nome para criar o primeiro.
                            </p>
                        </div>
                    </div>

                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Selecione um cliente ou digite um nome novo para cadastrá-lo.</p>
                    @error('client_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    @error('client_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs text-gray-500 dark:text-gray-400">O cliente pode ser selecionado da lista ou criado digitando um nome novo.</p>
                <x-ui.action type="submit" icon="plus">Adicionar cliente</x-ui.action>
            </div>
        </form>
            </div>
        </div>
    </section>

    <div
        x-cloak
        x-show="testEmailOpen"
        x-transition.opacity.duration.100ms
        x-on:keydown.escape.window="testEmailOpen = false"
        class="fixed inset-0 z-[100]"
        role="dialog"
        aria-modal="true"
        aria-labelledby="test-email-title"
    >
        <div class="absolute inset-0 bg-gray-900/50" x-on:click="testEmailOpen = false"></div>

        <div class="absolute left-1/2 top-1/2 w-full max-w-md -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-5 shadow-xl dark:bg-[#1c1c1b]">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h2 id="test-email-title" class="text-base font-semibold text-gray-900 dark:text-gray-100">Enviar e-mail de teste</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Usa as configurações de e-mail já salvas. Salve antes de testar, se tiver alterado algo.</p>
                </div>

                <button
                    type="button"
                    x-on:click="testEmailOpen = false"
                    class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10"
                    aria-label="Fechar"
                >
                    <x-icon name="x-mark" class="h-4 w-4" />
                </button>
            </div>

            <form method="POST" action="{{ route('admin.follow-ups.settings.test-email') }}" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label for="test_email_recipient" class="{{ $labelClass }}">Destinatário</label>
                    <input
                        id="test_email_recipient"
                        name="recipient"
                        type="email"
                        value="{{ old('recipient', auth()->user()->email) }}"
                        required
                        class="{{ $inputClass }}"
                    >
                    @error('recipient')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                </div>

                <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-4 dark:border-white/10">
                    <x-ui.action type="submit" icon="paper-airplane">Enviar teste</x-ui.action>
                    <x-ui.action type="button" variant="secondary" x-on:click="testEmailOpen = false">Cancelar</x-ui.action>
                </div>
            </form>
        </div>
    </div>
    </div>
</x-layouts.admin>
