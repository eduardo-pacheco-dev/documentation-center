<?php

namespace Plugins\Stations\Settings;

use App\Settings\SettingsSection;
use Illuminate\Http\Request;
use Plugins\Stations\Models\StationStorageSetting;

class StationStorageSettingsSection extends SettingsSection
{
    public function id(): string
    {
        return 'stations-storage';
    }

    public function title(): string
    {
        return 'Armazenamento';
    }

    public function description(): string
    {
        return 'Escolha onde os arquivos das estações serão armazenados.';
    }

    public function icon(): string
    {
        return 'folder';
    }

    public function view(): string
    {
        return 'stations::settings.storage';
    }

    public function order(): int
    {
        return 50;
    }

    public function visible(Request $request): bool
    {
        return (bool) $request->user()?->is_admin;
    }

    /**
     * The data passed to the settings section view.
     *
     * @return array<string, mixed>
     */
    public function data(Request $request): array
    {
        return [
            'settings' => StationStorageSetting::current(),
        ];
    }
}
