<?php

declare(strict_types=1);

namespace Plugins\Analytics\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plugins\Analytics\Database\Factories\AnalyticsRecordFactory;

#[Fillable([
    'oc',
    'os',
    'data_criacao',
    'fornecedor',
    'fornecedor_fiscalizacao',
    'tipo_projeto',
    'etapa_processo',
    'tipo_elemento',
    'endereco_atendimento',
    'cidade',
    'estado',
    'node_name',
    'localizacao',
    'operador',
    'status',
])]
class AnalyticsRecord extends Model
{
    /** @use HasFactory<AnalyticsRecordFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'analytics_records';

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): AnalyticsRecordFactory
    {
        return AnalyticsRecordFactory::new();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_criacao' => 'date',
        ];
    }

    /**
     * Records considered accepted or concluded.
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $this->whereStatusTerms($query, (array) config('analytics.accepted_status_terms', []));
    }

    /**
     * Records still pending or blocked by an impeditive issue.
     */
    public function scopePending(Builder $query): Builder
    {
        return $this->whereStatusTerms($query, (array) config('analytics.pending_status_terms', []));
    }

    /**
     * Apply the dashboard filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $search = trim((string) ($filters['search'] ?? ''));

        return $query
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $term = '%'.$search.'%';

                    $query->where('oc', 'like', $term)
                        ->orWhere('os', 'like', $term)
                        ->orWhere('node_name', 'like', $term)
                        ->orWhere('localizacao', 'like', $term)
                        ->orWhere('cidade', 'like', $term)
                        ->orWhere('endereco_atendimento', 'like', $term);
                });
            })
            ->when(filled($filters['fornecedor'] ?? null), fn (Builder $query): Builder => $query->where('fornecedor', $filters['fornecedor']))
            ->when(filled($filters['status'] ?? null), fn (Builder $query): Builder => $query->where('status', $filters['status']))
            ->when(filled($filters['node_name'] ?? null), fn (Builder $query): Builder => $query->where('node_name', $filters['node_name']))
            ->when(filled($filters['start_date'] ?? null), fn (Builder $query): Builder => $query->whereDate('data_criacao', '>=', $filters['start_date']))
            ->when(filled($filters['end_date'] ?? null), fn (Builder $query): Builder => $query->whereDate('data_criacao', '<=', $filters['end_date']));
    }

    /**
     * Apply a set of case-insensitive status terms.
     *
     * @param  list<string>  $terms
     */
    private function whereStatusTerms(Builder $query, array $terms): Builder
    {
        if ($terms === []) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($terms): void {
            foreach ($terms as $term) {
                $query->orWhereRaw('LOWER(status) LIKE ?', ['%'.mb_strtolower($term).'%']);
            }
        });
    }
}
