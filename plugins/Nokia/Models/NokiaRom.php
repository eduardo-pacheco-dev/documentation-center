<?php

namespace Plugins\Nokia\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Plugins\Nokia\Database\Factories\NokiaRomFactory;

#[Fillable([
    'first_name',
    'last_name',
    'email',
    'phone',
])]
class NokiaRom extends Model
{
    /** @use HasFactory<NokiaRomFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'nokia_roms';

    /**
     * The factory shipped with the plugin.
     */
    protected static function newFactory(): NokiaRomFactory
    {
        return NokiaRomFactory::new();
    }

    /**
     * The sites associated with the ROM.
     */
    public function sites(): HasMany
    {
        return $this->hasMany(NokiaSite::class, 'nokia_rom_id');
    }

    /**
     * The full name of the ROM.
     */
    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
