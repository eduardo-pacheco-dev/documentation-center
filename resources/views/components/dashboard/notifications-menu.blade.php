<div class="relative">
    <x-ui.icon-button
        icon="bell"
        label="Notificações"
        @click="notificationsOpen = !notificationsOpen"
        x-bind:aria-expanded="notificationsOpen"
    >
        <span class="absolute right-2 top-2 h-1.5 w-1.5 rounded-full bg-rose-500 ring-2 ring-[#f4f4f2] dark:ring-[#171716]"></span>
    </x-ui.icon-button>

    <x-ui.dropdown show="notificationsOpen" close="notificationsOpen = false" class="right-0 top-full mt-2 w-80 overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Notificações</p>
            <button type="button" class="text-xs font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Marcar como lidas</button>
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            <a href="{{ route('admin.work-orders.index') }}" class="flex gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-rose-500"></span>
                <span class="min-w-0">
                    <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Ordens de serviço pendentes</span>
                    <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">Existem OS aguardando atendimento.</span>
                </span>
            </a>

            <a href="{{ route('admin.files.index') }}" class="flex gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-sky-500"></span>
                <span class="min-w-0">
                    <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Novos arquivos enviados</span>
                    <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">Documentos recentes estão disponíveis.</span>
                </span>
            </a>

            <a href="{{ route('admin.agenda.index') }}" class="flex gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                <span class="min-w-0">
                    <span class="block text-sm font-medium text-gray-900 dark:text-gray-100">Compromissos de hoje</span>
                    <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">Confira a agenda antes de encerrar o dia.</span>
                </span>
            </a>
        </div>
    </x-ui.dropdown>
</div>
