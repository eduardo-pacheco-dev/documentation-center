<x-layouts.admin title="Site {{ $site->site_id }}">
    @php
        $checklists = config('nokia.checklists', []);

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

    <nav class="flex flex-wrap items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500" aria-label="Trilha de navegação">
        <a href="{{ route('admin.nokia.sites.index') }}" class="font-medium text-gray-500 transition hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Sites Nokia</a>
        <x-icon name="chevron-right" class="h-3 w-3" />
        <span class="truncate text-gray-400 dark:text-gray-500">{{ $site->site_id }}</span>
    </nav>

    <x-ui.page-header
        category="Nokia"
        title="{{ $site->site_id }}"
        description="Detalhes do site, checklist de atividades e anexos."
        icon="signal"
        class="mt-4"
    >
        <x-ui.action
            :href="route('admin.nokia.sites.index')"
            variant="secondary"
            icon="chevron-left"
        >
            Voltar
        </x-ui.action>

        <x-ui.action
            type="button"
            icon="pencil-square"
            x-on:click="$dispatch('open-edit-site-modal', @js($editPayload))"
        >
            Editar site
        </x-ui.action>
    </x-ui.page-header>

    @if ($errors->any())
        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50 p-4 dark:border-red-900/60 dark:bg-red-950/40">
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

    @php
        $checklistTotal = count($checklists);
        $checklistDone = collect($checklists)->keys()
            ->filter(fn (string $field): bool => (bool) $site->{$field})
            ->count();
        $checklistPercent = $checklistTotal > 0 ? (int) round(($checklistDone / $checklistTotal) * 100) : 0;
        $attachmentCount = $attachments->sum(fn (array $group): int => $group['files']->count());
    @endphp

    <div class="mt-6 grid items-start gap-4 lg:grid-cols-[minmax(0,1fr)_minmax(0,380px)]">
        <section class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Checklist de atividades</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Andamento das atividades de entrega e instalação.</p>
                </div>

                <span @class([
                    'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold',
                    'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' => $checklistDone === $checklistTotal,
                    'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' => $checklistDone !== $checklistTotal,
                ])>
                    {{ $checklistDone }} de {{ $checklistTotal }} concluídos
                </span>
            </div>

            <x-ui.progress-bar :percent="$checklistPercent" class="mt-4" />

            <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                @foreach ($checklists as $field => $label)
                    <li class="flex items-center gap-3 rounded-2xl border border-gray-200/80 px-4 py-3 dark:border-white/10">
                        <span @class([
                            'inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full',
                            'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' => $site->{$field},
                            'bg-gray-100 text-gray-400 dark:bg-white/10 dark:text-gray-500' => ! $site->{$field},
                        ])>
                            <x-icon :name="$site->{$field} ? 'check' : 'x-mark'" class="h-3.5 w-3.5" />
                        </span>

                        <span class="text-sm text-gray-700 dark:text-gray-200">{{ $label }}</span>

                        <x-ui.badge :tone="$site->{$field} ? 'green' : 'gray'" class="ml-auto">
                            {{ $site->{$field} ? 'Concluído' : 'Pendente' }}
                        </x-ui.badge>
                    </li>
                @endforeach
            </ul>
        </section>

        <aside class="space-y-4 lg:sticky lg:top-6">
            <section class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Informações</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Identificadores e ordens de serviço do site.</p>

                <dl class="mt-4 grid gap-2">
                    <div class="rounded-2xl border border-gray-200/80 bg-gray-50/60 px-4 py-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Site ID</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900 dark:text-white">{{ $site->site_id }}</dd>
                    </div>

                    <div class="rounded-2xl border border-gray-200/80 bg-gray-50/60 px-4 py-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">End ID</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900 dark:text-white">{{ $site->end_id ?: '—' }}</dd>
                    </div>

                    <div class="rounded-2xl border border-gray-200/80 bg-gray-50/60 px-4 py-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">OC</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900 dark:text-white">{{ $site->oc ?: '—' }}</dd>
                    </div>

                    <div class="rounded-2xl border border-gray-200/80 bg-gray-50/60 px-4 py-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">OS FAM Entrega</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900 dark:text-white">{{ $site->os_fam_entrega ?: '—' }}</dd>
                    </div>

                    <div class="rounded-2xl border border-gray-200/80 bg-gray-50/60 px-4 py-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">OS FAM Instalação</dt>
                        <dd class="mt-1 font-mono text-sm text-gray-900 dark:text-white">{{ $site->os_fam_instalacao ?: '—' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-3xl border border-gray-200/80 bg-white p-5 dark:border-white/10 dark:bg-[#171716]">
                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Registro</h2>

                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Criado em</dt>
                        <dd class="font-medium text-gray-700 dark:text-gray-200">{{ $site->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-gray-500 dark:text-gray-400">Atualizado em</dt>
                        <dd class="font-medium text-gray-700 dark:text-gray-200">{{ $site->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>
                </dl>
            </section>
        </aside>
    </div>

    <section class="mt-4 rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Anexos</h2>
                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Arquivos TSSR, DOC-D e DWG deste site.</p>
            </div>

            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                {{ $attachmentCount }} arquivo(s)
            </span>
        </div>

        <div class="mt-4 grid gap-3 md:grid-cols-3">
            @foreach ($attachments as $group)
                <div class="flex flex-col rounded-2xl border border-gray-200/80 p-4 dark:border-white/10">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ $group['label'] }}</h3>
                        <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500">{{ $group['files']->count() }} arquivo(s)</span>
                    </div>

                    @if ($group['files']->isEmpty())
                        <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">Nenhum arquivo enviado.</p>
                    @else
                        <ul class="mt-3 divide-y divide-gray-100 dark:divide-white/10">
                            @foreach ($group['files'] as $attachment)
                                <li class="flex items-center gap-2 py-2">
                                    <x-icon name="paper-clip" class="h-4 w-4 shrink-0 text-gray-400 dark:text-gray-500" />

                                    <span class="min-w-0 flex-1 truncate text-sm text-gray-700 dark:text-gray-200">{{ $attachment->original_name }}</span>

                                    <a
                                        href="{{ route('admin.nokia.sites.attachments.download', [$site, $attachment]) }}"
                                        class="shrink-0 rounded-lg px-2 py-1 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-500/10"
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
                        class="mt-auto space-y-2 pt-3"
                    >
                        @csrf
                        <input type="hidden" name="category" value="{{ $group['category'] }}">

                        <input
                            type="file"
                            name="files[]"
                            multiple
                            accept=".pdf,.doc,.docx,.dwg,.dxf,.xls,.xlsx,.csv,.txt,.png,.jpg,.jpeg,.zip"
                            class="block w-full text-xs text-gray-500 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-indigo-600 dark:text-gray-400 dark:file:bg-indigo-500/10 dark:file:text-indigo-300"
                        >

                        <x-ui.action type="submit" icon="arrow-up-tray" size="sm" class="w-full justify-center">
                            Enviar
                        </x-ui.action>
                    </form>
                </div>
            @endforeach
        </div>
    </section>

    <x-nokia::partials.site-modal
        mode="edit"
        :site="$site"
    />
</x-layouts.admin>
