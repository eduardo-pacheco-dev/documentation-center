<x-layouts.admin title="Site {{ $site->site_id }}">
    @php
        $checklists = config('nokia.checklists', []);

        $checklistTotal = count($checklists);
        $checklistDone = collect($checklists)->keys()
            ->filter(fn (string $field): bool => $site->checklistStatus($field) === 'sent')
            ->count();
        $checklistPercent = $checklistTotal > 0 ? (int) round(($checklistDone / $checklistTotal) * 100) : 0;

        $attachmentCount = $attachments->sum(fn (array $group): int => $group['files']->count());

        $tasks = $tasks ?? collect();
        $completedTaskIds = $completedTaskIds ?? [];
        $taskTotal = $tasks->count();
        $taskDone = $tasks->filter(fn ($task): bool => in_array($task->getKey(), $completedTaskIds, true))->count();
        $taskPercent = $taskTotal > 0 ? (int) round(($taskDone / $taskTotal) * 100) : 0;

        $progressTotal = $checklistTotal + $taskTotal;
        $progressDone = $checklistDone + $taskDone;
        $progressPercent = $progressTotal > 0 ? (int) round(($progressDone / $progressTotal) * 100) : 0;

        $isInterrupted = $site->isInterrupted();
        $interruptionReturnAt = $site->interruptionReturnAt();

        $isRejected = $site->isRejected();
        $rejectionReturnAt = $site->rejectionReturnAt();

        $dateRows = [
            ['label' => 'MOS', 'plan' => 'mos_plan_at', 'real' => 'mos_real_at'],
            ['label' => 'Instalação', 'plan' => 'instalacao_plan_at', 'real' => 'instalacao_real_at'],
            ['label' => 'Integração', 'plan' => 'integracao_plan_at', 'real' => 'integracao_real_at'],
            ['label' => 'RFA', 'plan' => 'rfa_plan_at', 'real' => 'rfa_real_at'],
        ];

        $approvalDates = [
            'fam_entrega_approved_at' => 'FAM Entrega aprovado',
            'fam_instalacao_approved_at' => 'FAM Instalação aprovado',
            'fam_panoramica_approved_at' => 'FAM Panorâmica aprovado',
        ];

        $hasDates = $site->hasDates();

        $ringRadius = 9;
        $ringCircumference = 2 * M_PI * $ringRadius;
        $ringOffset = $ringCircumference * (1 - ($progressPercent / 100));

        $editPayload = [
            'id' => $site->getKey(),
            'action' => route('admin.nokia.sites.update', $site),
            'fields' => collect(config('nokia.fields', []))
                ->pluck('name')
                ->merge(array_keys($checklists))
                ->push('nokia_rom_id')
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

                            <x-ui.badge :tone="$progressPercent === 100 ? 'green' : 'amber'">
                                {{ $progressPercent === 100 ? 'Progresso concluído' : 'Progresso pendente' }}
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
                                                'stroke-emerald-500' => $progressPercent === 100,
                                                'stroke-indigo-600 dark:stroke-indigo-500' => $progressPercent !== 100,
                                            ])
                                        />
                                    </svg>
                                </span>

                                Progresso {{ $progressDone }}/{{ $progressTotal }}
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
                        variant="secondary"
                        icon="exclamation-triangle"
                        x-on:click="$dispatch('open-interruption-modal')"
                    >
                        {{ $isInterrupted ? 'Editar interrupção' : 'Interromper obra' }}
                    </x-ui.action>

                    <x-ui.action
                        type="button"
                        variant="secondary"
                        icon="x-circle"
                        x-on:click="$dispatch('open-rejection-modal')"
                    >
                        {{ $isRejected ? 'Editar rejeição' : 'Rejeitar' }}
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
                @if ($isInterrupted)
                    <section class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Interrupção de obra</h2>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Motivo da parada, início e prazo de retorno.</p>
                            </div>

                            <x-ui.badge tone="amber">Obra interrompida</x-ui.badge>
                        </div>
                        <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50/70 p-4 dark:border-amber-500/30 dark:bg-amber-500/10">
                            <dl class="space-y-2.5 text-sm">
                                <div class="flex flex-col gap-0.5 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                    <dt class="text-amber-700/80 dark:text-amber-300/80">Motivo da parada</dt>
                                    <dd class="font-medium text-amber-900 dark:text-amber-100 sm:text-right">{{ $site->interruption_reason }}</dd>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-amber-700/80 dark:text-amber-300/80">Início</dt>
                                    <dd class="font-medium text-amber-900 dark:text-amber-100">{{ $site->interruption_started_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-amber-700/80 dark:text-amber-300/80">Prazo de retorno</dt>
                                    <dd class="font-medium text-amber-900 dark:text-amber-100">{{ $site->interruption_return_days }} dias · {{ $interruptionReturnAt?->format('d/m/Y') ?? '—' }}</dd>
                                </div>
                            </dl>

                            <form
                                method="POST"
                                action="{{ route('admin.nokia.sites.interruption.destroy', $site) }}"
                                data-confirm="Encerrar a interrupção de obra deste site?"
                                data-confirm-title="Encerrar interrupção"
                                data-confirm-label="Encerrar"
                                class="mt-4"
                            >
                                @csrf
                                @method('DELETE')

                                <x-ui.action type="submit" size="sm" variant="secondary" icon="play">Encerrar interrupção</x-ui.action>
                            </form>
                        </div>
                    </section>
                @endif

                @if ($isRejected)
                    <section class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Rejeições</h2>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Motivo da rejeição, início e prazo de retorno.</p>
                            </div>

                            <x-ui.badge tone="red">Obra rejeitada</x-ui.badge>
                        </div>

                        <div class="mt-4 rounded-2xl border border-red-200 bg-red-50/70 p-4 dark:border-red-500/30 dark:bg-red-500/10">
                            <dl class="space-y-2.5 text-sm">
                                <div class="flex flex-col gap-0.5 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                                    <dt class="text-red-700/80 dark:text-red-300/80">Motivo da rejeição</dt>
                                    <dd class="font-medium text-red-900 dark:text-red-100 sm:text-right">{{ $site->rejection_reason }}</dd>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-red-700/80 dark:text-red-300/80">Início</dt>
                                    <dd class="font-medium text-red-900 dark:text-red-100">{{ $site->rejection_started_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                                </div>

                                <div class="flex items-center justify-between gap-4">
                                    <dt class="text-red-700/80 dark:text-red-300/80">Prazo de retorno</dt>
                                    <dd class="font-medium text-red-900 dark:text-red-100">{{ $site->rejection_return_days }} dias · {{ $rejectionReturnAt?->format('d/m/Y') ?? '—' }}</dd>
                                </div>
                            </dl>

                            <form
                                method="POST"
                                action="{{ route('admin.nokia.sites.rejection.destroy', $site) }}"
                                data-confirm="Encerrar a rejeição deste site?"
                                data-confirm-title="Encerrar rejeição"
                                data-confirm-label="Encerrar"
                                class="mt-4"
                            >
                                @csrf
                                @method('DELETE')

                                <x-ui.action type="submit" size="sm" variant="secondary" icon="play">Encerrar rejeição</x-ui.action>
                            </form>
                        </div>
                    </section>
                @endif

                <section class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Datas</h2>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Cronograma planejado, realizado e aprovações das FAMs.</p>
                        </div>

                        <x-ui.action
                            type="button"
                            size="sm"
                            variant="secondary"
                            icon="calendar-days"
                            x-on:click="$dispatch('open-dates-modal')"
                        >
                            Editar datas
                        </x-ui.action>
                    </div>

                    @if (! $hasDates)
                        <div class="mt-4 rounded-2xl border border-dashed border-gray-200 px-4 py-8 text-center dark:border-white/10">
                            <span class="mx-auto inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 text-gray-400 dark:bg-white/[0.04] dark:text-gray-500">
                                <x-icon name="calendar-days" class="h-5 w-5" />
                            </span>
                            <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-200">Nenhuma data informada</p>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Clique em Editar datas para preencher o cronograma.</p>
                        </div>
                    @else
                        <div class="mt-4 overflow-hidden rounded-2xl border border-gray-200/80 dark:border-white/10">
                            <table class="min-w-full divide-y divide-gray-100 text-sm dark:divide-white/10">
                                <thead class="bg-gray-50/80 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:bg-white/[0.03] dark:text-gray-400">
                                    <tr>
                                        <th class="px-4 py-2.5">Etapa</th>
                                        <th class="px-4 py-2.5">Plan</th>
                                        <th class="px-4 py-2.5">Real</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-white/10">
                                    @foreach ($dateRows as $dateRow)
                                        <tr>
                                            <td class="px-4 py-2.5 font-medium text-gray-700 dark:text-gray-200">{{ $dateRow['label'] }}</td>
                                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-300">{{ $site->{$dateRow['plan']}?->format('d/m/Y') ?? '—' }}</td>
                                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-300">{{ $site->{$dateRow['real']}?->format('d/m/Y') ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <dl class="mt-3 space-y-2">
                            @foreach ($approvalDates as $field => $label)
                                <div class="flex items-center justify-between gap-4 rounded-xl bg-gray-50/70 px-3 py-2 text-sm dark:bg-white/[0.03]">
                                    <dt class="text-gray-600 dark:text-gray-300">{{ $label }}</dt>
                                    <dd class="font-medium text-gray-900 dark:text-white">{{ $site->{$field}?->format('d/m/Y') ?? '—' }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @endif
                </section>

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
                            @php($status = $site->checklistStatus($field))

                            <li class="py-2 first:pt-0 last:pb-0">
                                <form method="POST" action="{{ route('admin.nokia.sites.checklist.update', [$site, $field]) }}" class="flex flex-wrap items-center gap-3 py-1.5">
                                    @csrf
                                    @method('PUT')

                                    <span @class([
                                        'inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl',
                                        'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' => $status === 'sent',
                                        'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' => $status === 'analysis',
                                        'bg-red-50 text-red-600 dark:bg-red-500/15 dark:text-red-400' => $status === 'rejected',
                                        'bg-gray-100 text-gray-400 dark:bg-white/10 dark:text-gray-500' => $status === 'pending',
                                    ])>
                                        <x-icon :name="match ($status) {
                                            'sent' => 'check',
                                            'analysis' => 'clock',
                                            'rejected' => 'x-mark',
                                            default => 'minus',
                                        }" class="h-4 w-4" />
                                    </span>

                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $label }}</span>

                                    <div class="ml-auto flex items-center gap-2">
                                        <x-ui.badge :tone="$site->checklistStatusTone($field)">
                                            {{ $site->checklistStatusLabel($field) }}
                                        </x-ui.badge>

                                        <label for="checklist-status-{{ $field }}" class="sr-only">Status de {{ $label }}</label>
                                        <select
                                            id="checklist-status-{{ $field }}"
                                            name="status"
                                            class="h-9 rounded-xl border border-gray-300 bg-white px-2.5 text-xs font-medium text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                        >
                                            @foreach (\Plugins\Nokia\Models\NokiaSite::CHECKLIST_STATUSES as $value => $statusLabel)
                                                <option value="{{ $value }}" @selected($status === $value)>{{ $statusLabel }}</option>
                                            @endforeach
                                        </select>

                                        <button
                                            type="submit"
                                            class="rounded-lg p-1.5 text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-500/10"
                                            aria-label="Salvar status de {{ $label }}"
                                        >
                                            <x-icon name="check" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </form>
                            </li>
                        @endforeach
                    </ol>
                </section>

                <section class="rounded-3xl border border-gray-200/80 bg-white p-5 sm:p-6 dark:border-white/10 dark:bg-[#171716]">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Tarefas</h2>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tarefas cadastradas nas configurações do Nokia.</p>
                        </div>

                        <span @class([
                            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold',
                            'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400' => $taskTotal > 0 && $taskPercent === 100,
                            'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' => $taskTotal === 0,
                            'bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' => $taskTotal > 0 && $taskPercent !== 100,
                        ])>
                            {{ $taskDone }}/{{ $taskTotal }}
                        </span>
                    </div>

                    @if ($taskTotal > 0)
                        <x-ui.progress-bar :percent="$taskPercent" height="h-1.5" class="mt-4" />
                    @endif

                    @if ($tasks->isEmpty())
                        <div class="mt-4 rounded-2xl border border-dashed border-gray-200 px-4 py-8 text-center dark:border-white/10">
                            <span class="mx-auto inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 text-gray-400 dark:bg-white/[0.04] dark:text-gray-500">
                                <x-icon name="clipboard-document-list" class="h-5 w-5" />
                            </span>
                            <p class="mt-2 text-sm font-medium text-gray-700 dark:text-gray-200">Nenhuma tarefa cadastrada</p>
                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                @if (auth()->user()?->is_admin)
                                    <a href="{{ route('admin.nokia.settings.index') }}" class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Cadastre tarefas nas configurações</a>.
                                @else
                                    Peça a um administrador para cadastrar as tarefas nas configurações.
                                @endif
                            </p>
                        </div>
                    @else
                        <ul class="mt-5 divide-y divide-gray-100 dark:divide-white/10">
                            @foreach ($tasks as $task)
                                @php($taskCompleted = in_array($task->getKey(), $completedTaskIds, true))

                                <li class="py-1 first:pt-0 last:pb-0">
                                    <form method="POST" action="{{ route('admin.nokia.sites.tasks.toggle', [$site, $task]) }}" class="flex w-full items-center gap-3 py-3 text-left">
                                        @csrf

                                        <button
                                            type="submit"
                                            @class([
                                                'inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-xl transition',
                                                'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500/20' => $taskCompleted,
                                                'bg-gray-100 text-gray-400 hover:bg-gray-200 dark:bg-white/10 dark:text-gray-500 dark:hover:bg-white/15' => ! $taskCompleted,
                                            ])
                                            aria-label="{{ $taskCompleted ? 'Reabrir' : 'Concluir' }} a tarefa {{ $task->name }}"
                                        >
                                            <x-icon :name="$taskCompleted ? 'check' : 'plus'" class="h-4 w-4" />
                                        </button>

                                        <span @class([
                                            'text-sm font-medium',
                                            'text-gray-400 line-through dark:text-gray-500' => $taskCompleted,
                                            'text-gray-700 dark:text-gray-200' => ! $taskCompleted,
                                        ])>
                                            {{ $task->name }}
                                        </span>

                                        <x-ui.badge :tone="$taskCompleted ? 'green' : 'gray'" class="ml-auto">
                                            {{ $taskCompleted ? 'Concluída' : 'Pendente' }}
                                        </x-ui.badge>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                    @endif
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
                            <dt class="text-gray-500 dark:text-gray-400">ROM</dt>
                            <dd class="truncate font-medium text-gray-900 dark:text-white">{{ $site->rom?->fullName() ?: '—' }}</dd>
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

    <div
        x-data="{
            open: @js($errors->hasAny(['rejection_reason', 'rejection_return_days'])),
            init() {
                if (this.open) {
                    this.$nextTick(() => this.show());
                }
            },
            show() {
                if (this.$refs.dialog.open) {
                    return;
                }

                this.$refs.dialog.showModal();
                document.body.classList.add('overflow-hidden');

                this.$nextTick(() => this.$refs.dialog.querySelector('[data-autofocus]')?.focus());
            },
            close() {
                this.$refs.dialog.close();
            },
            onClose() {
                document.body.classList.remove('overflow-hidden');
            },
            onBackdropClick(event) {
                if (event.target === this.$refs.dialog) {
                    this.close();
                }
            },
        }"
        x-on:open-rejection-modal.window="show()"
    >
        <dialog
            id="rejection-modal"
            x-ref="dialog"
            x-on:close="onClose()"
            x-on:click="onBackdropClick($event)"
            class="m-auto max-h-[calc(100dvh-1.5rem)] w-[calc(100%-1.5rem)] max-w-lg overflow-hidden rounded-2xl bg-white p-0 text-gray-900 shadow-2xl dark:bg-[#1c1c1b] dark:text-gray-100"
            aria-labelledby="rejection-modal-title"
        >
            <div class="flex max-h-[calc(100dvh-1.5rem)] flex-col">
                <header class="flex shrink-0 items-start justify-between gap-3 border-b border-gray-100 px-4 py-3.5 sm:px-6 sm:py-4 dark:border-white/10">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400">
                            <x-icon name="x-circle" class="h-5 w-5" />
                        </span>

                        <div class="min-w-0">
                            <h2 id="rejection-modal-title" class="truncate text-base font-semibold sm:text-lg">Rejeição</h2>
                            <p class="mt-0.5 hidden text-xs text-gray-500 sm:block dark:text-gray-400">
                                Informe o motivo da rejeição e o prazo de retorno.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        x-on:click="close()"
                        class="rounded-full p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-white/10"
                        aria-label="Fechar"
                    >
                        <x-icon name="x-mark" class="h-5 w-5" />
                    </button>
                </header>

                <form method="POST" action="{{ route('admin.nokia.sites.rejection.update', $site) }}" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    @method('PUT')

                    <div class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6 sm:py-5">
                        @if ($errors->hasAny(['rejection_reason', 'rejection_return_days']))
                            <div class="rounded-xl border border-red-200 bg-red-50 p-3 dark:border-red-900/60 dark:bg-red-950/40">
                                <p class="flex items-center gap-2 text-sm font-semibold text-red-700 dark:text-red-300">
                                    <x-icon name="exclamation-triangle" class="h-4 w-4 shrink-0" />
                                    Não foi possível registrar a rejeição
                                </p>
                            </div>
                        @endif

                        <div>
                            <label for="rejection-reason" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Motivo da rejeição</label>
                            <textarea
                                id="rejection-reason"
                                name="rejection_reason"
                                rows="3"
                                required
                                data-autofocus
                                placeholder="Descreva o motivo da rejeição"
                                @error('rejection_reason') aria-invalid="true" @enderror
                                class="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            >{{ old('rejection_reason', $site->rejection_reason) }}</textarea>
                            @error('rejection_reason')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="rejection-return-days" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Prazo de retorno</label>
                            <select
                                id="rejection-return-days"
                                name="rejection_return_days"
                                required
                                @error('rejection_return_days') aria-invalid="true" @enderror
                                class="mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            >
                                <option value="">Selecione o prazo</option>
                                @foreach ([7, 14, 21, 28] as $days)
                                    <option value="{{ $days }}" @selected((int) old('rejection_return_days', $site->rejection_return_days) === $days)>{{ $days }} dias</option>
                                @endforeach
                            </select>
                            @error('rejection_return_days')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <footer class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-gray-100 px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-6 sm:py-4 dark:border-white/10">
                        <x-ui.action type="button" variant="secondary" x-on:click="close()">Cancelar</x-ui.action>
                        <x-ui.action type="submit" icon="x-circle">Registrar rejeição</x-ui.action>
                    </footer>
                </form>
            </div>
        </dialog>
    </div>

    <div
        x-data="{
            open: @js($errors->hasAny(\Plugins\Nokia\Models\NokiaSite::DATE_FIELDS)),
            init() {
                if (this.open) {
                    this.$nextTick(() => this.show());
                }
            },
            show() {
                if (this.$refs.dialog.open) {
                    return;
                }

                this.$refs.dialog.showModal();
                document.body.classList.add('overflow-hidden');

                this.$nextTick(() => this.$refs.dialog.querySelector('[data-autofocus]')?.focus());
            },
            close() {
                this.$refs.dialog.close();
            },
            onClose() {
                document.body.classList.remove('overflow-hidden');
            },
            onBackdropClick(event) {
                if (event.target === this.$refs.dialog) {
                    this.close();
                }
            },
        }"
        x-on:open-dates-modal.window="show()"
    >
        <dialog
            id="dates-modal"
            x-ref="dialog"
            x-on:close="onClose()"
            x-on:click="onBackdropClick($event)"
            class="m-auto max-h-[calc(100dvh-1.5rem)] w-[calc(100%-1.5rem)] max-w-2xl overflow-hidden rounded-2xl bg-white p-0 text-gray-900 shadow-2xl dark:bg-[#1c1c1b] dark:text-gray-100"
            aria-labelledby="dates-modal-title"
        >
            <div class="flex max-h-[calc(100dvh-1.5rem)] flex-col">
                <header class="flex shrink-0 items-start justify-between gap-3 border-b border-gray-100 px-4 py-3.5 sm:px-6 sm:py-4 dark:border-white/10">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400">
                            <x-icon name="calendar-days" class="h-5 w-5" />
                        </span>

                        <div class="min-w-0">
                            <h2 id="dates-modal-title" class="truncate text-base font-semibold sm:text-lg">Datas</h2>
                            <p class="mt-0.5 hidden text-xs text-gray-500 sm:block dark:text-gray-400">
                                Preencha o cronograma planejado, realizado e as aprovações das FAMs.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        x-on:click="close()"
                        class="rounded-full p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-white/10"
                        aria-label="Fechar"
                    >
                        <x-icon name="x-mark" class="h-5 w-5" />
                    </button>
                </header>

                <form method="POST" action="{{ route('admin.nokia.sites.dates.update', $site) }}" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    @method('PUT')

                    <div class="min-h-0 flex-1 space-y-5 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6 sm:py-5">
                        @if ($errors->hasAny(\Plugins\Nokia\Models\NokiaSite::DATE_FIELDS))
                            <div class="rounded-xl border border-red-200 bg-red-50 p-3 dark:border-red-900/60 dark:bg-red-950/40">
                                <p class="flex items-center gap-2 text-sm font-semibold text-red-700 dark:text-red-300">
                                    <x-icon name="exclamation-triangle" class="h-4 w-4 shrink-0" />
                                    Não foi possível salvar as datas
                                </p>
                            </div>
                        @endif

                        <fieldset>
                            <legend class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Cronograma</legend>

                            <div class="mt-3 space-y-4">
                                @foreach ($dateRows as $dateRow)
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label for="dates-{{ $dateRow['plan'] }}" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ $dateRow['label'] }} Plan</label>
                                            <input
                                                id="dates-{{ $dateRow['plan'] }}"
                                                name="{{ $dateRow['plan'] }}"
                                                type="text"
                                                inputmode="numeric"
                                                maxlength="10"
                                                placeholder="dd/mm/aaaa"
                                                value="{{ old($dateRow['plan'], $site->{$dateRow['plan']}?->format('d/m/Y')) }}"
                                                @error($dateRow['plan']) aria-invalid="true" @enderror
                                                class="mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                            >
                                            @error($dateRow['plan'])
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label for="dates-{{ $dateRow['real'] }}" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ $dateRow['label'] }} Real</label>
                                            <input
                                                id="dates-{{ $dateRow['real'] }}"
                                                name="{{ $dateRow['real'] }}"
                                                type="text"
                                                inputmode="numeric"
                                                maxlength="10"
                                                placeholder="dd/mm/aaaa"
                                                value="{{ old($dateRow['real'], $site->{$dateRow['real']}?->format('d/m/Y')) }}"
                                                @error($dateRow['real']) aria-invalid="true" @enderror
                                                class="mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                            >
                                            @error($dateRow['real'])
                                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>

                        <fieldset>
                            <legend class="text-sm font-semibold tracking-tight text-gray-900 dark:text-white">Aprovações FAM</legend>

                            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                                @foreach ($approvalDates as $field => $label)
                                    <div>
                                        <label for="dates-{{ $field }}" class="block text-sm font-medium text-gray-700 dark:text-gray-200">{{ $label }}</label>
                                        <input
                                            id="dates-{{ $field }}"
                                            name="{{ $field }}"
                                            type="text"
                                            inputmode="numeric"
                                            maxlength="10"
                                            placeholder="dd/mm/aaaa"
                                            value="{{ old($field, $site->{$field}?->format('d/m/Y')) }}"
                                            @error($field) aria-invalid="true" @enderror
                                            class="mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                                        >
                                        @error($field)
                                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>
                    </div>

                    <footer class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-gray-100 px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-6 sm:py-4 dark:border-white/10">
                        <x-ui.action type="button" variant="secondary" x-on:click="close()">Cancelar</x-ui.action>
                        <x-ui.action type="submit" icon="check">Salvar datas</x-ui.action>
                    </footer>
                </form>
            </div>
        </dialog>
    </div>

    <div
        x-data="{
            open: @js($errors->hasAny(['reason', 'return_days'])),
            init() {
                if (this.open) {
                    this.$nextTick(() => this.show());
                }
            },
            show() {
                if (this.$refs.dialog.open) {
                    return;
                }

                this.$refs.dialog.showModal();
                document.body.classList.add('overflow-hidden');

                this.$nextTick(() => this.$refs.dialog.querySelector('[data-autofocus]')?.focus());
            },
            close() {
                this.$refs.dialog.close();
            },
            onClose() {
                document.body.classList.remove('overflow-hidden');
            },
            onBackdropClick(event) {
                if (event.target === this.$refs.dialog) {
                    this.close();
                }
            },
        }"
        x-on:open-interruption-modal.window="show()"
    >
        <dialog
            id="interruption-modal"
            x-ref="dialog"
            x-on:close="onClose()"
            x-on:click="onBackdropClick($event)"
            class="m-auto max-h-[calc(100dvh-1.5rem)] w-[calc(100%-1.5rem)] max-w-lg overflow-hidden rounded-2xl bg-white p-0 text-gray-900 shadow-2xl dark:bg-[#1c1c1b] dark:text-gray-100"
            aria-labelledby="interruption-modal-title"
        >
            <div class="flex max-h-[calc(100dvh-1.5rem)] flex-col">
                <header class="flex shrink-0 items-start justify-between gap-3 border-b border-gray-100 px-4 py-3.5 sm:px-6 sm:py-4 dark:border-white/10">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="mt-0.5 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400">
                            <x-icon name="exclamation-triangle" class="h-5 w-5" />
                        </span>

                        <div class="min-w-0">
                            <h2 id="interruption-modal-title" class="truncate text-base font-semibold sm:text-lg">Interrupção de obra</h2>
                            <p class="mt-0.5 hidden text-xs text-gray-500 sm:block dark:text-gray-400">
                                Informe o motivo da parada e o prazo de retorno.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        x-on:click="close()"
                        class="rounded-full p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-500 dark:hover:bg-white/10"
                        aria-label="Fechar"
                    >
                        <x-icon name="x-mark" class="h-5 w-5" />
                    </button>
                </header>

                <form method="POST" action="{{ route('admin.nokia.sites.interruption.update', $site) }}" class="flex min-h-0 flex-1 flex-col">
                    @csrf
                    @method('PUT')

                    <div class="min-h-0 flex-1 space-y-4 overflow-y-auto overscroll-contain px-4 py-4 sm:px-6 sm:py-5">
                        @if ($errors->hasAny(['reason', 'return_days']))
                            <div class="rounded-xl border border-red-200 bg-red-50 p-3 dark:border-red-900/60 dark:bg-red-950/40">
                                <p class="flex items-center gap-2 text-sm font-semibold text-red-700 dark:text-red-300">
                                    <x-icon name="exclamation-triangle" class="h-4 w-4 shrink-0" />
                                    Não foi possível registrar a interrupção
                                </p>
                            </div>
                        @endif

                        <div>
                            <label for="interruption-reason" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Motivo da parada</label>
                            <textarea
                                id="interruption-reason"
                                name="reason"
                                rows="3"
                                required
                                data-autofocus
                                placeholder="Descreva o motivo da interrupção da obra"
                                @error('reason') aria-invalid="true" @enderror
                                class="mt-1.5 w-full rounded-xl border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            >{{ old('reason', $site->interruption_reason) }}</textarea>
                            @error('reason')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="interruption-return-days" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Prazo de retorno</label>
                            <select
                                id="interruption-return-days"
                                name="return_days"
                                required
                                @error('return_days') aria-invalid="true" @enderror
                                class="mt-1.5 h-10 w-full rounded-xl border border-gray-300 bg-white px-3 text-sm text-gray-900 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
                            >
                                <option value="">Selecione o prazo</option>
                                @foreach ([7, 14, 21, 28] as $days)
                                    <option value="{{ $days }}" @selected((int) old('return_days', $site->interruption_return_days) === $days)>{{ $days }} dias</option>
                                @endforeach
                            </select>
                            @error('return_days')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <footer class="flex shrink-0 flex-wrap items-center justify-end gap-2 border-t border-gray-100 px-4 py-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] sm:px-6 sm:py-4 dark:border-white/10">
                        <x-ui.action type="button" variant="secondary" x-on:click="close()">Cancelar</x-ui.action>
                        <x-ui.action type="submit" icon="exclamation-triangle">Registrar interrupção</x-ui.action>
                    </footer>
                </form>
            </div>
        </dialog>
    </div>

    <x-nokia::partials.site-modal
        mode="edit"
        :site="$site"
        :roms="$roms"
    />
</x-layouts.admin>
