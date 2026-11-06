<x-layouts.admin title="Editar ROM {{ $rom->fullName() }}">
    @php
        $inputClass = 'mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100';
        $labelClass = 'block text-sm font-medium text-gray-700 dark:text-gray-200';
    @endphp

    <nav class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500" aria-label="Trilha de navegação">
        <a href="{{ route('admin.nokia.sites.index') }}" class="font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Sites Nokia</a>
        <x-icon name="chevron-right" class="h-3 w-3" />
        <a href="{{ route('admin.nokia.settings.index') }}" class="font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Configurações</a>
        <x-icon name="chevron-right" class="h-3 w-3" />
        <span class="truncate text-gray-400 dark:text-gray-500">{{ $rom->fullName() }}</span>
    </nav>

    <x-ui.page-header
        category="Nokia"
        title="Editar ROM"
        description="Atualize os dados de contato do ROM."
        icon="user"
        class="mt-4"
    >
        <x-ui.action :href="route('admin.nokia.settings.index')" variant="secondary" icon="chevron-left">
            Voltar
        </x-ui.action>
    </x-ui.page-header>

    <div class="mt-6 max-w-2xl rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <form method="POST" action="{{ route('admin.nokia.settings.roms.update', $rom) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="rom-first-name" class="{{ $labelClass }}">Nome</label>
                    <input
                        id="rom-first-name"
                        name="first_name"
                        type="text"
                        value="{{ old('first_name', $rom->first_name) }}"
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
                        value="{{ old('last_name', $rom->last_name) }}"
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
                    value="{{ old('email', $rom->email) }}"
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
                    value="{{ old('phone', $rom->phone) }}"
                    required
                    placeholder="(11) 99999-9999"
                    @error('phone') aria-invalid="true" @enderror
                    class="{{ $inputClass }}"
                >
                @error('phone')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-5 dark:border-white/10">
                <x-ui.action type="submit" icon="check">Salvar alterações</x-ui.action>
                <x-ui.action :href="route('admin.nokia.settings.index')" variant="secondary">Cancelar</x-ui.action>
            </div>
        </form>
    </div>
</x-layouts.admin>
