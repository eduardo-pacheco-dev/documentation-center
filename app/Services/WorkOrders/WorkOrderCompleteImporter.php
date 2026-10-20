<?php

namespace App\Services\WorkOrders;

use App\Enums\WorkOrderItemStatus;
use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class WorkOrderCompleteImporter
{
    /**
     * Import work orders and their line items from the given rows.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return int The number of work orders created or updated.
     *
     * @throws ValidationException
     */
    public function import(User $user, array $rows): int
    {
        $clientIds = $user->clients()
            ->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name): array => [mb_strtolower(trim((string) $name)) => $id])
            ->all();

        $catalogItems = $user->catalogItems()
            ->get(['id', 'name', 'unit', 'price'])
            ->keyBy(fn ($item): string => mb_strtolower(trim((string) $item->name)));

        $prepared = [];
        $errors = [];

        foreach (array_values($rows) as $index => $row) {
            $line = $index + 2;

            $row = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $row);
            $row['oc'] = $this->identifier($row['oc'] ?? null);
            $row['chave'] = $this->identifier($row['chave'] ?? null);

            $validator = Validator::make($row, [
                'cliente' => ['required', 'string', 'max:255'],
                'erb' => ['nullable', 'string', 'max:255'],
                'radio_link' => ['nullable', 'string', 'max:200'],
                'descricao' => ['nullable', 'string', 'max:2000'],
                'prioridade' => ['nullable', 'string'],
                'status' => ['nullable', 'string'],
                'abertura' => ['nullable'],
                'previsao' => ['nullable'],
                'observacoes' => ['nullable', 'string', 'max:2000'],
                'oc' => ['nullable', 'string', 'max:60', 'regex:/^\d+$/'],
                'chave' => ['nullable', 'string', 'max:60'],
                'item' => ['required', 'string', 'max:255'],
                'quantidade' => ['required', 'numeric', 'min:0.01'],
                'status_do_item' => ['nullable', 'string'],
                'os_cliente_1' => ['nullable', 'string', 'max:60'],
                'os_cliente_2' => ['nullable', 'string', 'max:60'],
            ], [
                'oc.regex' => 'a OC deve conter apenas números.',
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Linha {$line}: {$message}";
                }

                continue;
            }

            $data = $validator->validated();

            $oc = $this->identifier($data['oc'] ?? null);
            $chave = $this->identifier($data['chave'] ?? null);

            if ($oc === null && $chave === null) {
                $errors[] = "Linha {$line}: informe a OC ou a CHAVE para agrupar as linhas da mesma OS.";

                continue;
            }

            $erbRef = filled($data['erb'] ?? null) ? trim((string) $data['erb']) : null;
            $radioLinkRef = filled($data['radio_link'] ?? null) ? trim((string) $data['radio_link']) : null;

            if ($erbRef !== null && $radioLinkRef !== null) {
                $errors[] = "Linha {$line}: informe a ERB ou o radio link, não ambos.";
            }

            $radioLinkEndpoints = $radioLinkRef !== null ? $this->parseRadioLinkEndpoints($radioLinkRef) : null;

            if ($radioLinkEndpoints !== null && strcasecmp($radioLinkEndpoints[0], $radioLinkEndpoints[1]) === 0) {
                $errors[] = "Linha {$line}: as pontas do radio link devem ser diferentes.";
            }

            $priority = $this->parsePriority($data['prioridade'] ?? null);
            $status = $this->parseStatus($data['status'] ?? null);
            $itemStatus = $this->parseItemStatus($data['status_do_item'] ?? null);

            if ($priority === null) {
                $errors[] = "Linha {$line}: prioridade inválida (use baixa, normal, alta ou urgente).";
            }

            if ($status === null) {
                $errors[] = "Linha {$line}: status inválido (use aberta, em andamento, em espera, concluída ou cancelada).";
            }

            if ($itemStatus === null) {
                $errors[] = "Linha {$line}: status do item inválido (use pendente, em andamento, concluído, rejeitado ou cancelado).";
            }

            $openedAt = $this->parseDate($data['abertura'] ?? null);
            $dueAt = $this->parseDate($data['previsao'] ?? null);

            if ($this->hasValue($data['abertura'] ?? null) && $openedAt === null) {
                $errors[] = "Linha {$line}: data de abertura inválida (use dd/mm/aaaa).";
            }

            if ($this->hasValue($data['previsao'] ?? null) && $dueAt === null) {
                $errors[] = "Linha {$line}: data de previsão inválida (use dd/mm/aaaa).";
            }

            $openedAt ??= now()->startOfDay();

            if ($dueAt !== null && $dueAt->lt($openedAt)) {
                $errors[] = "Linha {$line}: a previsão de entrega deve ser posterior à abertura.";
            }

            $quantity = (float) $data['quantidade'];
            $itemName = trim((string) $data['item']);
            $catalogItem = $catalogItems->get(mb_strtolower($itemName));
            $unitPrice = round((float) ($catalogItem?->price ?? 0), 2);

            if ($priority === null || $status === null || $itemStatus === null) {
                continue;
            }

            $prepared[] = [
                'line' => $line,
                'group' => $oc !== null ? 'OC:'.$oc : 'CHAVE:'.mb_strtoupper($chave),
                'client_name' => trim((string) $data['cliente']),
                'erb_ref' => $erbRef,
                'radio_link_ref' => $radioLinkRef,
                'radio_link_endpoints' => $radioLinkEndpoints,
                'oc' => $oc,
                'chave' => $chave,
                'description' => $data['descricao'] ?? null,
                'priority' => $priority->value,
                'status' => $status->value,
                'opened_at' => $openedAt->toDateString(),
                'due_at' => $dueAt?->toDateString(),
                'completed_at' => $status === WorkOrderStatus::Completed ? $openedAt->toDateString() : null,
                'notes' => $data['observacoes'] ?? null,
                'item' => [
                    'catalog_item_id' => $catalogItem?->getKey(),
                    'description' => $catalogItem?->name ?? $itemName,
                    'client_os' => $this->identifier($data['os_cliente_1'] ?? null),
                    'client_os_2' => $this->identifier($data['os_cliente_2'] ?? null),
                    'unit' => $catalogItem?->unit,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => round($quantity * $unitPrice, 2),
                    'status' => $itemStatus->value,
                ],
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['import' => $errors]);
        }

        $groups = collect($prepared)->groupBy('group');

        $resolvedGroups = [];

        foreach ($groups as $groupRows) {
            $first = $groupRows->first();
            $line = $first['line'];

            $existingByOc = $first['oc'] !== null
                ? WorkOrder::query()->where('oc', $first['oc'])->first()
                : null;
            $existingByChave = $first['chave'] !== null
                ? WorkOrder::query()->where('chave', $first['chave'])->first()
                : null;

            if ($existingByOc !== null && $existingByChave !== null && ! $existingByOc->is($existingByChave)) {
                $errors[] = "Linha {$line}: a OC e a CHAVE informadas pertencem a ordens de serviço diferentes.";

                continue;
            }

            $existing = $existingByOc ?? $existingByChave;

            if ($existing !== null && $existing->user_id !== $user->getKey()) {
                $errors[] = "Linha {$line}: a OC ou CHAVE informada já está em uso por outra ordem de serviço.";

                continue;
            }

            if ($first['oc'] !== null && $existingByOc === null && WorkOrder::withTrashed()->where('oc', $first['oc'])->exists()) {
                $errors[] = "Linha {$line}: a OC \"{$first['oc']}\" já está em uso por uma ordem de serviço excluída.";

                continue;
            }

            if ($first['chave'] !== null && $existingByChave === null && WorkOrder::withTrashed()->where('chave', $first['chave'])->exists()) {
                $errors[] = "Linha {$line}: a CHAVE \"{$first['chave']}\" já está em uso por uma ordem de serviço excluída.";

                continue;
            }

            $resolvedGroups[] = [
                'existing_id' => $existing?->getKey(),
                'rows' => $groupRows->values()->all(),
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['import' => $errors]);
        }

        return DB::transaction(function () use ($user, $clientIds, $resolvedGroups): int {
            $erbIds = [];
            $radioLinkIds = [];

            $this->primeRelationCaches($user, $erbIds, $radioLinkIds);

            foreach ($resolvedGroups as $group) {
                $rows = $group['rows'];
                $first = $rows[0];

                $clientId = $this->resolveClientId($user, $first['client_name'], $clientIds);
                $erbId = $first['erb_ref'] !== null
                    ? $this->resolveErbId($user, $first['erb_ref'], $erbIds)
                    : null;

                $radioLinkId = null;

                if (is_array($first['radio_link_endpoints'])) {
                    [$endA, $endB] = $first['radio_link_endpoints'];

                    $endAId = $this->resolveErbId($user, $endA, $erbIds);
                    $endBId = $this->resolveErbId($user, $endB, $erbIds);

                    $radioLinkId = $this->resolveRadioLinkId($user, $endAId, $endBId, $radioLinkIds);
                } elseif ($first['radio_link_ref'] !== null) {
                    $radioLinkId = $radioLinkIds[mb_strtoupper($first['radio_link_ref'])] ?? null;
                }

                $attributes = [
                    'client_id' => $clientId,
                    'erb_id' => $erbId,
                    'radio_link_id' => $radioLinkId,
                    'oc' => $first['oc'],
                    'chave' => $first['chave'],
                    'description' => $first['description'],
                    'priority' => $first['priority'],
                    'status' => $first['status'],
                    'opened_at' => $first['opened_at'],
                    'due_at' => $first['due_at'],
                    'completed_at' => $first['completed_at'],
                    'notes' => $first['notes'],
                ];

                if ($group['existing_id'] !== null) {
                    $workOrder = WorkOrder::query()
                        ->where('user_id', $user->getKey())
                        ->findOrFail($group['existing_id']);

                    $workOrder->update($attributes);
                } else {
                    $attributes['number'] = WorkOrder::generateNumber($user);

                    $workOrder = $user->workOrders()->create($attributes);
                }

                $workOrder->items()->delete();
                $workOrder->items()->createMany(array_map(
                    fn (array $row): array => $row['item'],
                    $rows,
                ));
                $workOrder->update(['total' => $workOrder->items()->sum('total')]);
            }

            return count($resolvedGroups);
        });
    }

    /**
     * Resolve a client by name, creating it when missing.
     *
     * @param  array<string, int>  $clientIds
     */
    private function resolveClientId(User $user, string $name, array &$clientIds): int
    {
        $key = mb_strtolower(trim($name));

        if (isset($clientIds[$key])) {
            return $clientIds[$key];
        }

        $client = $user->clients()->create(['name' => trim($name)]);

        return $clientIds[$key] = $client->getKey();
    }

    /**
     * Prime the lookup caches with the user's ERBs and radio links.
     *
     * @param  array<string, int>  $erbIds
     * @param  array<string, int>  $radioLinkIds
     */
    private function primeRelationCaches(User $user, array &$erbIds, array &$radioLinkIds): void
    {
        foreach ($user->erbs()->get(['id', 'site_id', 'code', 'end_id']) as $erb) {
            foreach ([$erb->site_id, $erb->code, $erb->end_id] as $key) {
                if (filled($key)) {
                    $erbIds[mb_strtoupper((string) $key)] = $erb->getKey();
                }
            }
        }

        foreach ($user->radioLinks()->get(['id', 'code', 'erb_a_id', 'erb_b_id']) as $radioLink) {
            if (filled($radioLink->code)) {
                $radioLinkIds[mb_strtoupper((string) $radioLink->code)] = $radioLink->getKey();
            }

            $radioLinkIds[$this->radioLinkKey((int) $radioLink->erb_a_id, (int) $radioLink->erb_b_id)] = $radioLink->getKey();
        }
    }

    /**
     * Resolve an ERB by its site id, code or end id, creating it when missing.
     *
     * @param  array<string, int>  $erbIds
     */
    private function resolveErbId(User $user, string $reference, array &$erbIds): int
    {
        $reference = trim($reference);
        $key = mb_strtoupper($reference);

        if (isset($erbIds[$key])) {
            return $erbIds[$key];
        }

        $erb = $user->erbs()->create([
            'site_id' => $reference,
            'name' => $reference,
        ]);

        return $erbIds[$key] = $erb->getKey();
    }

    /**
     * Resolve a radio link by its endpoints, creating it when missing.
     *
     * @param  array<string, int>  $radioLinkIds
     */
    private function resolveRadioLinkId(User $user, int $endAId, int $endBId, array &$radioLinkIds): int
    {
        $key = $this->radioLinkKey($endAId, $endBId);

        if (isset($radioLinkIds[$key])) {
            return $radioLinkIds[$key];
        }

        $radioLink = $user->radioLinks()->create([
            'erb_a_id' => $endAId,
            'erb_b_id' => $endBId,
        ]);

        return $radioLinkIds[$key] = $radioLink->getKey();
    }

    /**
     * The cache key of a radio link regardless of its direction.
     */
    private function radioLinkKey(int $endAId, int $endBId): string
    {
        return min($endAId, $endBId).':'.max($endAId, $endBId);
    }

    /**
     * Parse the endpoints of a radio link cell.
     *
     * @return array{0: string, 1: string}|null
     */
    private function parseRadioLinkEndpoints(string $value): ?array
    {
        $parts = preg_split('/\s+(?:dir|[-–—])\s+/iu', trim($value), 2);

        if ($parts === false || count($parts) !== 2) {
            return null;
        }

        $endA = trim($parts[0]);
        $endB = trim($parts[1]);

        if ($endA === '' || $endB === '') {
            return null;
        }

        return [$endA, $endB];
    }

    /**
     * Whether the given cell contains a value.
     */
    private function hasValue(mixed $value): bool
    {
        return $value !== null && trim((string) $value) !== '';
    }

    /**
     * Normalize an optional identifier cell.
     */
    private function identifier(mixed $value): ?string
    {
        return $this->hasValue($value) ? trim((string) $value) : null;
    }

    /**
     * Parse the priority label or enum value from a spreadsheet cell.
     */
    private function parsePriority(mixed $value): ?WorkOrderPriority
    {
        $value = mb_strtolower(trim((string) $value));

        if ($value === '') {
            return WorkOrderPriority::Normal;
        }

        return match ($value) {
            'baixa', 'low' => WorkOrderPriority::Low,
            'normal' => WorkOrderPriority::Normal,
            'alta', 'high' => WorkOrderPriority::High,
            'urgente', 'urgent' => WorkOrderPriority::Urgent,
            default => null,
        };
    }

    /**
     * Parse the status label or enum value from a spreadsheet cell.
     */
    private function parseStatus(mixed $value): ?WorkOrderStatus
    {
        $value = mb_strtolower(trim((string) $value));

        if ($value === '') {
            return WorkOrderStatus::Open;
        }

        return match ($value) {
            'aberta', 'open' => WorkOrderStatus::Open,
            'em andamento', 'em_andamento', 'in_progress' => WorkOrderStatus::InProgress,
            'em espera', 'em_espera', 'on_hold' => WorkOrderStatus::OnHold,
            'concluída', 'concluida', 'completed' => WorkOrderStatus::Completed,
            'cancelada', 'cancelled' => WorkOrderStatus::Cancelled,
            default => null,
        };
    }

    /**
     * Parse the item status label or enum value from a spreadsheet cell.
     */
    private function parseItemStatus(mixed $value): ?WorkOrderItemStatus
    {
        $value = mb_strtolower(trim((string) $value));

        if ($value === '') {
            return WorkOrderItemStatus::Pending;
        }

        return match ($value) {
            'pendente', 'pending' => WorkOrderItemStatus::Pending,
            'em andamento', 'em_andamento', 'in_progress' => WorkOrderItemStatus::InProgress,
            'concluído', 'concluido', 'completed' => WorkOrderItemStatus::Completed,
            'rejeitado', 'rejected' => WorkOrderItemStatus::Rejected,
            'cancelado', 'cancelled' => WorkOrderItemStatus::Cancelled,
            default => null,
        };
    }

    /**
     * Parse a date cell, accepting Excel serials and common string formats.
     */
    private function parseDate(mixed $value): ?Carbon
    {
        if (! $this->hasValue($value)) {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->startOfDay();
            } catch (Throwable) {
                return null;
            }
        }

        $value = trim((string) $value);

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);

                if ($date !== false) {
                    return $date->startOfDay();
                }
            } catch (Throwable) {
                // Try the next format.
            }
        }

        return null;
    }
}
