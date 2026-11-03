@props([
    'station',
    'category',
    'title',
    'description',
    'files',
    'icon' => 'paper-clip',
    'emptyTitle' => 'Nenhum arquivo enviado',
    'emptyDescription' => 'Envie o primeiro arquivo para começar.',
    'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.txt,.csv,.png,.jpg,.jpeg,.webp,.kml,.kmz',
])

@php
    $inputId = 'station-files-'.str_replace('_', '-', $category);
@endphp

<section {{ $attributes->class(['scroll-mt-24 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]']) }}>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                <x-icon :name="$icon" class="h-5 w-5" />
            </span>

            <div>
                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">{{ $title }}</h2>
                <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>
            </div>
        </div>

        <x-ui.badge :tone="$files->isNotEmpty() ? 'indigo' : 'gray'">
            {{ $files->count() }} {{ $files->count() === 1 ? 'arquivo' : 'arquivos' }}
        </x-ui.badge>
    </div>

    <div class="mt-5">
        @if ($files->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/60 px-5 py-8 text-center dark:border-white/10 dark:bg-white/[0.02]">
                <x-icon :name="$icon" class="mx-auto h-6 w-6 text-gray-400 dark:text-gray-500" />
                <p class="mt-3 text-sm font-medium text-gray-700 dark:text-gray-200">{{ $emptyTitle }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $emptyDescription }}</p>
            </div>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-white/10">
                @foreach ($files as $file)
                    <li class="flex flex-wrap items-center gap-3 py-3.5">
                        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-gray-200 bg-gray-50 text-[10px] font-bold tracking-wide text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                            {{ $file->extension() }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-100">{{ $file->original_name }}</p>

                            <p class="mt-0.5 flex flex-wrap items-center gap-x-1.5 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ $file->humanSize() }}</span>
                                <span aria-hidden="true">·</span>
                                <span>{{ $file->uploader?->full_name ?? 'Usuário removido' }}</span>
                                <span aria-hidden="true">·</span>
                                <x-relative-time :value="$file->created_at" />
                            </p>
                        </div>

                        <div class="flex items-center gap-1">
                            <a
                                href="{{ route('admin.stations.attachments.download', [$station, $file]) }}"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-gray-500 transition hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/10 dark:hover:text-white"
                                aria-label="Baixar {{ $file->original_name }}"
                                title="Baixar arquivo"
                            >
                                <x-icon name="arrow-down-tray" class="h-4 w-4" />
                            </a>

                            <form
                                method="POST"
                                action="{{ route('admin.stations.attachments.destroy', [$station, $file]) }}"
                                data-confirm-delete="Remover o arquivo {{ $file->original_name }}? Essa ação não pode ser desfeita."
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl text-red-500 transition hover:bg-red-50 hover:text-red-600 dark:text-red-400 dark:hover:bg-red-500/15"
                                    aria-label="Remover {{ $file->original_name }}"
                                    title="Remover arquivo"
                                >
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <form
        method="POST"
        action="{{ route('admin.stations.attachments.store', $station) }}"
        enctype="multipart/form-data"
        class="mt-5"
        x-data="{ selection: [] }"
    >
        @csrf

        <input type="hidden" name="category" value="{{ $category }}">

        <label
            for="{{ $inputId }}"
            class="flex cursor-pointer flex-col items-center justify-center rounded-2xl border border-dashed border-gray-300 bg-gray-50/60 px-5 py-7 text-center transition hover:border-indigo-300 hover:bg-indigo-50/40 dark:border-gray-700 dark:bg-white/[0.02] dark:hover:border-indigo-500/40 dark:hover:bg-indigo-500/[0.06]"
        >
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-indigo-600 shadow-sm dark:bg-white/10 dark:text-indigo-300">
                <x-icon name="arrow-up-tray" class="h-5 w-5" />
            </span>

            <span class="mt-3 text-sm font-semibold text-gray-800 dark:text-gray-100">Selecionar arquivos</span>
            <span class="mt-1 text-xs text-gray-500 dark:text-gray-400">PDF, documentos, planilhas, imagens, KML/KMZ ou ZIP · até 20 MB por arquivo</span>

            <input
                id="{{ $inputId }}"
                type="file"
                name="files[]"
                multiple
                accept="{{ $accept }}"
                class="sr-only"
                x-on:change="selection = Array.from($event.target.files).map(file => file.name)"
            >
        </label>

        <div x-show="selection.length > 0" x-cloak class="mt-3 rounded-xl border border-indigo-100 bg-indigo-50/60 px-4 py-3 dark:border-indigo-500/20 dark:bg-indigo-500/[0.08]">
            <p class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">Arquivos selecionados</p>
            <ul class="mt-1.5 space-y-1">
                <template x-for="name in selection" :key="name">
                    <li class="truncate text-xs text-indigo-700/80 dark:text-indigo-200/80" x-text="name"></li>
                </template>
            </ul>
        </div>

        @error('files')
            <x-ui.field-error class="mt-3">{{ $message }}</x-ui.field-error>
        @enderror

        @error('files.*')
            <x-ui.field-error class="mt-3">{{ $message }}</x-ui.field-error>
        @enderror

        <div class="mt-3 flex justify-end">
            <x-ui.action type="submit" icon="paper-clip" size="sm">
                Anexar arquivos
            </x-ui.action>
        </div>
    </form>
</section>
