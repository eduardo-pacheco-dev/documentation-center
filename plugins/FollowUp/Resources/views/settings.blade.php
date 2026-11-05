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

    <section class="mt-4 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon name="building-office" class="h-5 w-5" />
            </span>

            <div>
                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Clientes e projetos</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre os clientes e os projetos de cada cliente usados nos follow-ups.</p>
            </div>
        </div>

                        <form
            method="POST"
            action="{{ route('admin.follow-ups.settings.clients.store') }}"
            class="mt-5 rounded-2xl border border-gray-200/80 p-4 dark:border-white/10"
            x-data="{
                clientId: @js(old('client_id', $clients->isEmpty() ? 'new' : '')),
                get newClient() {
                    return this.clientId === 'new';
                },
            }"
        >
            @csrf

            <div class="grid gap-3 sm:grid-cols-2">
                <div>
                    <label for="client_id" class="{{ $labelClass }}">Cliente</label>
                    <select id="client_id" name="client_id" x-model="clientId" required class="{{ $inputClass }}">
                        <option value="">Selecione um cliente</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->getKey() }}">{{ $client->name }}</option>
                        @endforeach
                        <option value="new">+ Novo cliente</option>
                    </select>

                    <div x-cloak x-show="newClient" class="mt-2">
                        <label for="client_name" class="{{ $labelClass }}">Nome do novo cliente</label>
                        <input
                            id="client_name"
                            name="client_name"
                            type="text"
                            maxlength="150"
                            value="{{ old('client_name') }}"
                            placeholder="Ex.: AFL Engenharia"
                            x-bind:required="newClient"
                            class="{{ $inputClass }}"
                        >
                        @error('client_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="project_name" class="{{ $labelClass }}">Projeto <span class="font-normal text-gray-400">(opcional para novo cliente)</span></label>
                    <input
                        id="project_name"
                        name="project_name"
                        type="text"
                        maxlength="150"
                        value="{{ old('project_name') }}"
                        placeholder="Ex.: Expansão 5G"
                        class="{{ $inputClass }}"
                    >
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                <p class="text-xs text-gray-500 dark:text-gray-400">Selecione um cliente para adicionar um projeto, ou escolha "Novo cliente".</p>
                <x-ui.action type="submit" icon="plus">Adicionar</x-ui.action>
            </div>

            @error('client_id')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            @error('project_name')<p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
        </form>

        <div class="mt-5 space-y-3">
            @forelse ($clients as $client)
                <div class="rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
                    <div class="flex flex-wrap items-center gap-2">
                        <form method="POST" action="{{ route('admin.follow-ups.settings.clients.update', $client) }}" class="flex min-w-0 flex-1 items-center gap-2">
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
                        </form>

                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $client->follow_ups_count }} follow-up(s)</span>

                        <form method="POST" action="{{ route('admin.follow-ups.settings.clients.destroy', $client) }}" data-confirm-delete="Remover o cliente {{ $client->name }}? Os projetos e vínculos dos follow-ups serão removidos.">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50 hover:text-red-600 dark:text-red-400 dark:hover:bg-red-500/15" aria-label="Remover cliente {{ $client->name }}">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </form>
                    </div>

                    <ul class="mt-3 space-y-2 border-t border-gray-100 pt-3 dark:border-white/10">
                        @forelse ($client->projects as $project)
                            <li class="flex flex-wrap items-center gap-2">
                                <form method="POST" action="{{ route('admin.follow-ups.settings.projects.update', $project) }}" class="grid min-w-0 flex-1 gap-2 sm:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)_auto]">
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

                                    <button type="submit" class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-indigo-600 px-3 text-xs font-semibold text-white transition hover:bg-indigo-500">
                                        <x-icon name="check" class="h-3.5 w-3.5" />
                                        Salvar
                                    </button>
                                </form>

                                <span class="text-xs text-gray-500 dark:text-gray-400">{{ $project->follow_ups_count }} follow-up(s)</span>

                                <form method="POST" action="{{ route('admin.follow-ups.settings.projects.destroy', $project) }}" data-confirm-delete="Remover o projeto {{ $project->name }}?">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50 hover:text-red-600 dark:text-red-400 dark:hover:bg-red-500/15" aria-label="Remover projeto {{ $project->name }}">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </form>
                            </li>
                        @empty
                            <li class="text-xs text-gray-500 dark:text-gray-400">Nenhum projeto cadastrado para este cliente.</li>
                        @endforelse
                    </ul>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 px-4 py-8 text-center text-sm text-gray-500 dark:border-white/15 dark:text-gray-400">
                    Nenhum cliente cadastrado. Use o formulário acima para começar.
                </div>
            @endforelse
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
