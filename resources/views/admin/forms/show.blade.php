<x-layouts.admin :title="$form->code">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="flex items-center gap-2 text-xl font-semibold">
                <x-icon name="clipboard-document-list" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                {{ $form->code }}
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $form->name }}</p>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <x-form-status :status="$form->status" />
                <span class="text-sm text-gray-500 dark:text-gray-400">{{ $form->type }}</span>
                @if ($form->project)
                    <a href="{{ route('admin.projects.show', $form->project) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                        [{{ $form->project->code }}] {{ $form->project->name }}
                    </a>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.forms.edit', $form) }}"
                class="inline-flex items-center gap-1.5 rounded-md bg-gray-900 dark:bg-white px-4 py-2 text-sm font-medium text-white dark:text-gray-900 hover:bg-gray-700 dark:hover:bg-gray-200"
            >
                <x-icon name="pencil-square" class="h-4 w-4" />
                Editar
            </a>

            <a
                href="{{ route('admin.forms.index') }}"
                class="rounded-md px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100"
            >
                Voltar
            </a>
        </div>
    </div>

    @php($steps = $form->steps ?? [])

    <div class="mt-6 space-y-4">
        @forelse ($steps as $step)
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center gap-2 border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <x-icon name="list-steps" class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ $step['title'] ?: 'Etapa' }}
                    </h2>
                    @if (!empty($step['id']))
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            {{ $step['id'] }}
                        </span>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3 font-medium">Campo</th>
                                <th class="px-5 py-3 font-medium">Valor</th>
                                <th class="px-5 py-3 text-right font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($step['fields'] ?? [] as $field)
                                <tr>
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $field['label'] ?: ($field['id'] ?: 'Campo') }}</p>
                                        @if (!empty($field['id']))
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $field['id'] }} &middot; {{ $field['type'] ?? 'text' }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3">
                                        @if (($field['type'] ?? null) === 'image')
                                            <span class="flex flex-wrap items-center gap-1">
                                                @forelse (($field['images'] ?? []) as $image)
                                                    <a
                                                        href="{{ $image }}"
                                                        target="_blank"
                                                        rel="noopener"
                                                        class="inline-flex items-center gap-0.5 rounded-md bg-gray-100 px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-indigo-400 dark:hover:bg-gray-700"
                                                    >
                                                        <x-icon name="photo" class="h-3.5 w-3.5" />
                                                        foto
                                                    </a>
                                                @empty
                                                    <span class="text-gray-500 dark:text-gray-400">Nenhuma foto.</span>
                                                @endforelse
                                            </span>
                                        @else
                                            <span class="whitespace-pre-wrap text-gray-700 dark:text-gray-300">{{ $field['value'] ?: '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <x-form-field-status :status="$field['status'] ?? null" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">Nenhum campo na etapa.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <p class="flex items-center gap-2 rounded-xl border border-dashed border-gray-300 px-5 py-8 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
                <x-icon name="list-steps" class="h-4 w-4" />
                Nenhuma etapa definida para este formulário.
            </p>
        @endforelse
    </div>

    <div class="mt-4 rounded-xl border border-gray-200 bg-white px-5 py-4 text-xs text-gray-500 shadow-sm dark:border-gray-800 dark:bg-gray-900 dark:text-gray-400">
        Criado em {{ $form->created_at->format('d/m/Y H:i') }}
        &middot;
        Atualizado em {{ $form->updated_at->format('d/m/Y H:i') }}
    </div>
</x-layouts.admin>