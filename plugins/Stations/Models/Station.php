<?php

namespace Plugins\Stations\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plugins\Stations\Database\Factories\StationFactory;

#[Fillable([
    'site_id',
    'endereco_id',
    'tipo_de_elemento',
    'tecnologia',
    'tipo_de_conexao',
    'classificacao',
    'detentor_area',
    'tipo_de_infra',
    'tipo_de_ev',
    'tipo_de_logradouro',
    'logradouro',
    'numero',
    'complemento',
    'bairro',
    'municipio',
    'estado',
    'cep',
    'regional',
    'latitude',
    'longitude',
    'status',
    'tipo_da_torre',
    'station_id',
])]
class Station extends Model
{
    /** @use HasFactory<StationFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'stations';

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): StationFactory
    {
        return StationFactory::new();
    }

    /**
     * Delete the stored attachments when the station is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (Station $station): void {
            $station->attachments()->get()->each->delete();
        });
    }

    /**
     * The files attached to the station.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(StationAttachment::class)->latest();
    }

    /**
     * The comments written about the station.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(StationComment::class)->latest();
    }

    /**
     * Filter the stations by a search term.
     */
    public function scopeMatching(Builder $query, string $term): void
    {
        $like = "%{$term}%";

        $query->where(function (Builder $query) use ($like): void {
            $query->where('site_id', 'like', $like)
                ->orWhere('station_id', 'like', $like)
                ->orWhere('endereco_id', 'like', $like)
                ->orWhere('tipo_de_elemento', 'like', $like)
                ->orWhere('tecnologia', 'like', $like)
                ->orWhere('logradouro', 'like', $like)
                ->orWhere('bairro', 'like', $like)
                ->orWhere('municipio', 'like', $like);
        });
    }

    /**
     * The human readable address of the station.
     */
    public function address(): string
    {
        $line = collect([
            $this->logradouro,
            $this->numero,
            $this->complemento,
        ])->filter()->implode(', ');

        return collect([$line, $this->bairro, $this->municipio])
            ->filter()
            ->implode(' — ');
    }
}
