@props(['title' => 'Dashboard'])

<header class="sticky top-0 z-20 border-b border-gray-200/80 bg-[#f4f4f2]/85 backdrop-blur-md dark:border-white/10 dark:bg-[#171716]/85">
    <div class="mx-auto flex h-16 w-full max-w-[1440px] items-center gap-3 px-4 sm:px-6 lg:px-8">
        <x-ui.icon-button
            icon="bars-3"
            label="Abrir menu"
            @click="sidebarOpen = true"
            class="lg:hidden"
        />

        <div class="hidden min-w-0 items-center gap-2 text-sm sm:flex">
            <span class="text-gray-400 dark:text-gray-500">Administração</span>
            <x-icon name="chevron-right" class="h-3.5 w-3.5 text-gray-300 dark:text-gray-600" />
            <span class="truncate font-medium text-gray-900 dark:text-gray-100">{{ $title }}</span>
        </div>

        <div class="ml-auto flex flex-1 items-center justify-end gap-2 sm:flex-none">
            <x-ui.search-input class="hidden w-full max-w-xs items-center md:flex lg:w-80" />

            <x-ui.icon-button icon="magnifying-glass" label="Pesquisar" class="md:hidden" />
        </div>
    </div>
</header>
