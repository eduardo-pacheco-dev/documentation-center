<?php

namespace App\Enums;

enum CatalogItemType: string
{
    case Product = 'product';
    case Service = 'service';

    /**
     * Get the human readable label for the type.
     */
    public function label(): string
    {
        return match ($this) {
            self::Product => 'Produto',
            self::Service => 'Serviço',
        };
    }
}
