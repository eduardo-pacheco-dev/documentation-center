<?php

namespace App\Services\WorkOrders;

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

class WorkOrderBulkImporter
{
    /**
     * Import the given rows as work orders for the user.
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return int The number of work orders created.
     *
     * @throws ValidationException
     */
    public function import(User $user, array $rows): int
    {
        $clients = $user->clients()
            ->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name): array => [mb_strtolower(trim((string) $name)) => $id]);

        $prepared = [];
        $errors = [];

        foreach (array_values($rows) as $index => $row) {
            $line = $index + 2;

            $row = array_map(fn ($value) => is_string($value) ? trim($value) : $value, $row);

            $validator = Validator::make($row, [
                'cliente' => ['required', 'string', 'max:255'],
                'titulo' => ['required', 'string', 'max:255'],
                'descricao' => ['nullable', 'string', 'max:2000'],
                'prioridade' => ['nullable', 'string'],
                'status' => ['nullable', 'string'],
                'abertura' => ['nullable'],
                'previsao' => ['nullable'],
                'observacoes' => ['nullable', 'string', 'max:2000'],
            ]);

            if ($validator->fails()) {
                foreach ($validator->errors()->all() as $message) {
                    $errors[] = "Linha {$line}: {$message}";
                }

                continue;
            }

            $data = $validator->validated();

            $clientId = $clients[mb_strtolower((string) $data['cliente'])] ?? null;

            if ($clientId === null) {
                $errors[] = "Linha {$line}: o cliente \"{$data['cliente']}\" não foi encontrado.";
            }

            $priority = $this->parsePriority($data['prioridade'] ?? null);
            $status = $this->parseStatus($data['status'] ?? null);

            if ($priority === null) {
                $errors[] = "Linha {$line}: prioridade inválida (use baixa, normal, alta ou urgente).";
            }

            if ($status === null) {
                $errors[] = "Linha {$line}: status inválido (use aberta, em andamento, concluída ou cancelada).";
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

            if ($clientId === null || $priority === null || $status === null) {
                continue;
            }

            $prepared[] = [
                'client_id' => $clientId,
                'title' => $data['titulo'],
                'description' => $data['descricao'] ?? null,
                'priority' => $priority->value,
                'status' => $status->value,
                'opened_at' => $openedAt->toDateString(),
                'due_at' => $dueAt?->toDateString(),
                'completed_at' => $status === WorkOrderStatus::Completed ? $openedAt->toDateString() : null,
                'notes' => $data['observacoes'] ?? null,
                'total' => 0,
            ];
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['import' => $errors]);
        }

        return DB::transaction(function () use ($user, $prepared): int {
            foreach ($prepared as $attributes) {
                $attributes['number'] = WorkOrder::generateNumber($user);

                $user->workOrders()->create($attributes);
            }

            return count($prepared);
        });
    }

    /**
     * Whether the given cell contains a value.
     */
    private function hasValue(mixed $value): bool
    {
        return $value !== null && trim((string) $value) !== '';
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
            'concluída', 'concluida', 'completed' => WorkOrderStatus::Completed,
            'cancelada', 'cancelled' => WorkOrderStatus::Cancelled,
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
