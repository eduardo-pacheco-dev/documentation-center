<x-layouts.admin title="Configurações Nokia">
    @php
        $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
        $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
    @endphp

    <x-ui.page-header
        category="Nokia"
        title="Configurações"
        description="Gerencie os ROMs que podem ser vinculados aos sites Nokia."
        icon="cog-6-tooth"
    >
        <x-ui.action :href="route('admin.nokia.sites.index')" variant="secondary" icon="chevron-left">
            Voltar para sites
        </x-ui.action>
    </x-ui.page-header>

    <div class="mt-6 grid items-start gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.35fr)]">
        <section class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
            <div class="flex items-start gap-3">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="users" class="h-5 w-5" />
                </span>

                <div class="min-w-0">
                    <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Novo ROM</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Cadastre o ROM com nome, sobrenome, e-mail e telefone.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.nokia.settings.roms.store') }}" class="mt-5 space-y-4">
                @csrf

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="rom-first-name" class="{{ $labelClass }}">Nome</label>
                        <input
                            id="rom-first-name"
                            name="first_name"
                            type="text"
                            value="{{ old('first_name') }}"
                            required
                            data-autofocus
                            @error('first_name') aria-invalid="true" @enderror
                            class="{{ $inputClass }}"
                        >
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="rom-last-name" class="{{ $labelClass }}">Sobrenome</label>
                        <input
                            id="rom-last-name"
                            name="last_name"
                            type="text"
                            value="{{ old('last_name') }}"
                            required
                            @error('last_name') aria-invalid="true" @enderror
                            class="{{ $inputClass }}"
                        >
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="rom-email" class="{{ $labelClass }}">E-mail</label>
                    <input
                        id="rom-email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        required
                        @error('email') aria-invalid="true" @enderror
                        class="{{ $inputClass }}"
                    >
                    @error('email')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="rom-phone" class="{{ $labelClass }}">Telefone</label>
                    <input
                        id="rom-phone"
                        name="phone"
                        type="text"
                        value="{{ old('phone') }}"
                        required
                        placeholder="(11) 99999-9999"
                        @error('phone') aria-invalid="true" @enderror
                        class="{{ $inputClass }}"
                    >
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="border-t border-gray-100 pt-4 dark:border-white/10">
                    <x-ui.action type="submit" icon="plus">Cadastrar ROM</x-ui.action>
                </div>
            </form>
        </section>

        <section id="roms" class="scroll-mt-6 rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]">
            <div class="flex items-start gap-3 p-5 sm:p-6">
                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                    <x-icon name="user-plus" class="h-5 w-5" />
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">ROMs</h2>
                        <x-ui.badge tone="indigo">{{ $roms->count() }}</x-ui.badge>
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Os ROMs cadastrados ficam disponíveis no campo ROM ao criar ou editar um site.</p>
                </div>
            </div>

            <div class="border-t border-gray-100 p-5 sm:p-6 dark:border-white/10">
                @if ($roms->isEmpty())
                    <x-ui.empty-state
                        icon="user-plus"
                        title="Nenhum ROM cadastrado"
                        description="Use o formulário ao lado para cadastrar o primeiro ROM."
                        tone="indigo"
                        shape="square"
                    />
                @else
                    <ul class="divide-y divide-gray-100 overflow-hidden rounded-2xl border border-gray-200/80 dark:divide-white/10 dark:border-white/10">
                        @foreach ($roms as $rom)
                            <li class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center">
                                <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gray-50 text-gray-500 dark:bg-white/5 dark:text-gray-400">
                                    <x-icon name="user" class="h-4 w-4" />
                                </span>

                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $rom->fullName() }}</p>

                                    <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="inline-flex items-center gap-1">
                                            <x-icon name="envelope" class="h-3.5 w-3.5" />
                                            {{ $rom->email }}
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <x-icon name="phone" class="h-3.5 w-3.5" />
                                            {{ $rom->phone }}
                                        </span>
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    <x-ui.action :href="route('admin.nokia.settings.roms.edit', $rom)" size="sm" variant="secondary" icon="pencil-square">
                                        Editar
                                    </x-ui.action>

                                    <form
                                        method="POST"
                                        action="{{ route('admin.nokia.settings.roms.destroy', $rom) }}"
                                        data-confirm="Excluir o ROM {{ $rom->fullName() }}? Os sites vinculados ficarão sem ROM."
                                        data-confirm-title="Excluir ROM"
                                        data-confirm-label="Excluir"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <x-ui.action type="submit" size="sm" variant="danger" icon="trash" aria-label="Excluir ROM {{ $rom->fullName() }}">
                                            Excluir
                                        </x-ui.action>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </section>
    </div>
</x-layouts.admin>
