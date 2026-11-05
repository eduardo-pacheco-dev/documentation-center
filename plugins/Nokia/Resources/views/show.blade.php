<x-layouts.admin title="Site {{ $site->site_id }}">
    @php
        $checklists = config('nokia.checklists', []);

        $checklistTotal = count($checklists);
        $checklistDone = collect($checklists)->keys()
            ->filter(fn (string $field): bool => (bool) $site->{$field})
            ->count();
        $checklistPercent = $checklistTotal > 0 ? (int) round(($checklistDone / $checklistTotal) * 100) : 0;

        $attachmentCount = $attachments->sum(fn (array $group): int => $group['files']->count());

        $ringRadius = 9;
        $ringCircumference = 2 * M_PI * $ringRadius;
        $ringOffset = $ringCircumference * (1 - ($checklistPercent / 100));

        $editPayload = [
            'id' => $site->getKey(),
            'action' => route('admin.nokia.sites.update', $site),
            'fields' => collect(config('nokia.fields', []))
                ->pluck('name')
                ->merge(array_keys($checklists))
                ->mapWithKeys(fn (string $name): array => [$name => $site->{$name}])
                ->all(),
        ];
    @endphp

    <div class="space-y-5">
        <nav class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500" aria-label="Trilha de navegação">
            <a href="{{ route('admin.nokia.sites.index') }}" class="font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Sites Nokia</a>
            <x-icon name="chevron-right" class="h-3 w-3" />
            <span class="truncate text-gray-400 dark:text-gray-500">{{ $site->site_id }}</span>
        </nav>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-900/60 dark:bg-red-950/40">
                <p class="flex items-center gap-2 text-sm font-semibold text-red-700 dark:text-red-300">
                    <x-icon name="exclamation-triangle" class="h-4 w-4 shrink-0" />
                    Não foi possível concluir a operação
                </p>

                <ul class="mt-2 space-y-1 pl-6 text-sm text-red-700 dark:text-red-300">
                    @foreach ($errors->all() as $message)
                        <li class="list-disc">{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="relative overflow-hidden rounded-3xl border border-gray-200/80 bg-white dark:border-white/10 dark:bg-[#171716]" x-data="{ edit: @js($editPayload) }">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-44 bg-linear-to-br from-indigo-500/15 via-violet-500/10 to-transparent dark:from-indigo-500/20 dark:via-violet-500/10"></div>

            <div class="relative flex flex-col gap-6 p-6 sm:p-8 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex min-w-0 items-start gap-4">
                    <span class="inline-flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-linear-to-br from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-600/25">
                        <x-icon name="signal" class="h-7 w-7" />
                    </span>

                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-400">Site Nokia</p>

                            <x-ui.badge :tone="$checklistPercent === 100 ? 'green' : 'amber'">
                                {{ $checklistPercent === 100 ? 'Checklist concluído' : 'Checklist pendente' }}
                            </x-ui.badge>
                        </div>

                        <h1 class="mt-1 truncate text-2xl font-semibold tracking-tight text-gray-900 sm:text-3xl dark:text-white">
                            {{ $site->site_id }}
                        </h1>

                        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200/80 bg-white/70 px-2.5 py-1 font-medium text-gray-600 backdrop-blur dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                <x-icon name="link" class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" />
                                End ID
                                <span class="font-mono text-gray-900 dark:text-white">{{ $site->end_id ?: '—' }}</span>
                            </span>

                            <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200/80 bg-white/70 px-2.5 py-1 font-medium text-gray-600 backdrop-blur dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                <x-icon name="document-text" class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" />
                                OC
                                <span class="font-mono text-gray-900 dark:text-white">{{ $site->oc ?: '—' }}</span>
                            </span>

                            <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200/80 bg-white/70 px-2.5 py-1 font-medium text-gray-600 backdrop-blur dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                <x-icon name="clock" class="h-3.5 w-3.5 text-gray-400 dark:text-gray-500" />
                                Atualizado {{ $site->updated_at?->format('d/m/Y H:i') ?? '—' }}
                            </span>

                            <span class="inline-flex items-center gap-1.5 rounded-full border border-gray-200/80 bg-white/70 px-2.5 py-1 font-medium text-gray-600 backdrop-blur dark:border-white/10 dark:bg-white/[0.06] dark:text-gray-300">
                                <span class="relative inline-flex h-4 w-4 items-center justify-center">
                                    <svg viewBox="0 0 24 24" class="h-4 w-4 -rotate-90" aria-hidden="true">
                                        <circle cx="12" cy="12" r="{{ $ringRadius }}" fill="none" stroke-width="3" class="stroke-gray-300 dark:stroke-white/20" />
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="{{ $ringRadius }}"
                                            fill="none"
                                            stroke-width="3"
                                            stroke-linecap="round"
                                            stroke-dasharray="{{ round($ringCircumference, 2) }}"
                                            stroke-dashoffset="{{ round($ringOffset, 2) }}"
                                            @class([
                                                'stroke-emerald-500' => $checklistPercent === 100,
                                                'stroke-indigo-600 dark:stroke-indigo-500' => $checklistPercent !== 100,
                                            ])
                                        />
                                    </svg>
                                </span>

                                Checklist {{ $checklistDone }}/{{ $checklistTotal }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2 xl:shrink-0">
                    <x-ui.action :href="route('admin.nokia.sites.index')" variant="secondary" icon="chevron-left">
                        Voltar
                    </x-ui.action>

                    <x-ui.action
                        type="button"
                        icon="pencil-square"
                        x-on:click="$dispatch('open-edit-site-modal', edit)"
                    >
                        Editar site
                    </x-ui.action>
                </div>
            </div>
        </section>

        <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="space-y-5">
                <section class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Atividades</h2>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Checklist de entrega e instalação do site.</p>
                        </div>

                        <span @class([
                            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold',
                            'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' => $checklistPercent === 100,
                            'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' => $checklistPercent !== 100,
                        ])>
                            {{ $checklistDone }}/{{ $checklistTotal }}
                        </span>
                    </div>

                    <x-ui.progress-bar :percent="$checklistPercent" height="h-1.5" class="mt-4" />

                    <ol class="mt-5 divide-y divide-gray-100 dark:divide-white/10">
                        @foreach ($checklists as $field => $label)
                            <li class="flex items-center gap-3 py-3.5 first:pt-0 last:pb-0">
                                <span @class([
                                    'inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl',
                                    'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' => $site->{$field},
                                    'bg-gray-100 text-gray-400 dark:bg-white/10 dark:text-gray-500' => ! $site->{$field},
                                ])>
                                    <x-icon :name="$site->{$field} ? 'check' : 'x-mark'" class="h-4 w-4" />
                                </span>

                                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $label }}</span>

                                <x-ui.badge :tone="$site->{$field} ? 'green' : 'gray'" class="ml-auto">
                                    {{ $site->{$field} ? 'Concluído' : 'Pendente' }}
                                </x-ui.badge>
                            </li>
                        @endforeach
                    </ol>
                </section>

                <section
                    class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]"
                    x-data="{ tab: @js($attachments->first()['category'] ?? 'tssr') }"
                >
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Anexos</h2>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Arquivos TSSR, DOC-D e DWG deste site.</p>
                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                            {{ $attachmentCount }} arquivo(s)
                        </span>
                    </div>

                    <div class="mt-4 flex gap-1 rounded-2xl bg-gray-100/80 p-1 dark:bg-white/[0.06]" role="tablist" aria-label="Categorias de anexos">
                        @foreach ($attachments as $group)
                            <button
                                type="button"
                                role="tab"
                                x-on:click="tab = @js($group['category'])"
                                x-bind:aria-selected="tab === @js($group['category']) ? 'true' : 'false'"
                                x-bind:class="tab === @js($group['category'])
                                    ? 'bg-white text-indigo-600 shadow-sm dark:bg-white/10 dark:text-indigo-300'
                                    : 'text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white'"
                                class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold transition"
                            >
                                {{ $group['label'] }}

                                <span class="rounded-full bg-gray-200/80 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                    {{ $group['files']->count() }}
                                </span>
                            </button>
                        @endforeach
                    </div>

                    @foreach ($attachments as $group)
                        <div x-cloak x-show="tab === @js($group['category'])" role="tabpanel" class="mt-4">
                            @if ($group['files']->isEmpty())
                                <div class="rounded-2xl border border-dashed border-gray-200 px-4 py-8 text-center dark:border-white/10">
                                    <span class="mx-auto inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 text-gray-400 dark:bg-white/[0.04] dark:text-gray-500">
                                        <x-icon name="paper-clip" class="h-5 w-5" />
                                    </span>
                                    <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-200">Nenhum arquivo de {{ $group['label'] }}</p>
                                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Envie o primeiro arquivo usando o campo abaixo.</p>
                                </div>
                            @else
                                <ul class="space-y-2">
                                    @foreach ($group['files'] as $attachment)
                                        <li class="flex items-center gap-3 rounded-2xl border border-gray-200/80 bg-gray-50/60 px-3 py-2.5 dark:border-white/10 dark:bg-white/[0.03]">
                                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-indigo-600 shadow-sm dark:bg-white/10 dark:text-indigo-300">
                                                <x-icon name="document-text" class="h-4 w-4" />
                                            </span>

                                            <div class="min-w-0 flex-1">
                                                <p class="truncate text-sm font-medium text-gray-800 dark:text-gray-100">{{ $attachment->original_name }}</p>
                                                <p class="text-[11px] text-gray-400 dark:text-gray-500">{{ $attachment->humanSize() }}</p>
                                            </div>

                                            <a
                                                href="{{ route('admin.nokia.sites.attachments.download', [$site, $attachment]) }}"
                                                class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-500/10"
                                            >
                                                Baixar
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route('admin.nokia.sites.attachments.destroy', [$site, $attachment]) }}"
                                                data-confirm-delete="Remover o anexo {{ $attachment->original_name }}? Essa ação não pode ser desfeita."
                                                class="shrink-0"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="rounded-lg p-1.5 text-red-500 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/15"
                                                    aria-label="Remover anexo"
                                                >
                                                    <x-icon name="trash" class="h-4 w-4" />
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            <form
                                method="POST"
                                action="{{ route('admin.nokia.sites.attachments.store', $site) }}"
                                enctype="multipart/form-data"
                                class="mt-3"
                                x-data="{ files: [] }"
                            >
                                @csrf
                                <input type="hidden" name="category" value="{{ $group['category'] }}">

                                <label
                                    for="attachments-{{ $group['category'] }}"
                                    class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-2xl border border-dashed border-gray-300 px-4 py-5 text-center transition hover:border-indigo-300 hover:bg-indigo-50/40 dark:border-white/15 dark:hover:border-indigo-500/40 dark:hover:bg-indigo-500/[0.06]"
                                >
                                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-300">
                                        <x-icon name="arrow-up-tray" class="h-4 w-4" />
                                    </span>

                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">Selecionar arquivos</span>
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500">PDF, DOC, DWG, XLS, imagens ou ZIP · até 10 MB cada</span>

                                    <input
                                        id="attachments-{{ $group['category'] }}"
                                        type="file"
                                        name="files[]"
                                        multiple
                                        accept=".pdf,.doc,.docx,.dwg,.dxf,.xls,.xlsx,.csv,.txt,.png,.jpg,.jpeg,.zip"
                                        class="sr-only"
                                        x-on:change="files = Array.from($event.target.files)"
                                    >
                                </label>

                                <div class="mt-2 flex flex-wrap items-center justify-between gap-2">
                                    <p class="truncate text-[11px] text-gray-500 dark:text-gray-400" x-show="files.length > 0" x-cloak>
                                        <span x-text="files.length"></span> arquivo(s) selecionado(s)
                                    </p>

                                    <x-ui.action
                                        type="submit"
                                        size="sm"
                                        icon="arrow-up-tray"
                                        class="ml-auto disabled:cursor-not-allowed disabled:opacity-50"
                                        x-bind:disabled="files.length === 0"
                                    >
                                        Enviar
                                    </x-ui.action>
                                </div>
                            </form>
                        </div>
                    @endforeach
                </section>
            </div>

            <aside class="space-y-5 xl:sticky xl:top-6">
                <section class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
                    <div class="flex items-center gap-2">
                        <x-icon name="signal" class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                        <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Informações</h2>
                    </div>

                    <dl class="mt-3 divide-y divide-gray-100 text-sm dark:divide-white/10">
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <dt class="text-gray-500 dark:text-gray-400">Site ID</dt>
                            <dd class="truncate font-mono font-medium text-gray-900 dark:text-white">{{ $site->site_id }}</dd>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <dt class="text-gray-500 dark:text-gray-400">End ID</dt>
                            <dd class="truncate font-mono font-medium text-gray-900 dark:text-white">{{ $site->end_id ?: '—' }}</dd>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <dt class="text-gray-500 dark:text-gray-400">OC</dt>
                            <dd class="truncate font-mono font-medium text-gray-900 dark:text-white">{{ $site->oc ?: '—' }}</dd>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <dt class="text-gray-500 dark:text-gray-400">OS FAM Entrega</dt>
                            <dd class="truncate font-mono font-medium text-gray-900 dark:text-white">{{ $site->os_fam_entrega ?: '—' }}</dd>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <dt class="text-gray-500 dark:text-gray-400">OS FAM Instalação</dt>
                            <dd class="truncate font-mono font-medium text-gray-900 dark:text-white">{{ $site->os_fam_instalacao ?: '—' }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
                    <div class="flex items-center gap-2">
                        <x-icon name="clock" class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                        <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Registro</h2>
                    </div>

                    <dl class="mt-3 divide-y divide-gray-100 text-sm dark:divide-white/10">
                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <dt class="text-gray-500 dark:text-gray-400">Criado em</dt>
                            <dd class="font-medium text-gray-700 dark:text-gray-200">{{ $site->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                        </div>

                        <div class="flex items-center justify-between gap-3 py-2.5">
                            <dt class="text-gray-500 dark:text-gray-400">Atualizado em</dt>
                            <dd class="font-medium text-gray-700 dark:text-gray-200">{{ $site->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                        </div>
                    </dl>
                </section>
            </aside>
        </div>
    </div>

    <x-nokia::partials.site-modal
        mode="edit"
        :site="$site"
    />
</x-layouts.admin>
