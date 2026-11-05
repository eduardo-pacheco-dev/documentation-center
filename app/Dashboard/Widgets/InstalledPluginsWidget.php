<?php

namespace App\Dashboard\Widgets;

use App\Dashboard\Widget;
use App\Plugins\PluginRepository;
use Illuminate\Http\Request;

/**
 * Total number of plugins discovered on disk.
 */
class InstalledPluginsWidget extends Widget
{
    public function __construct(private readonly PluginRepository $plugins) {}

    public function id(): string
    {
        return 'core.plugins.installed';
    }

    public function title(): string
    {
        return 'Plugins instalados';
    }

    public function description(): ?string
    {
        return 'Plugins encontrados no diretório de plugins.';
    }

    public function icon(): ?string
    {
        return 'squares-2x2';
    }

    public function view(): string
    {
        return 'admin.widgets.installed-plugins';
    }

    public function order(): int
    {
        return 20;
    }

    public function data(Request $request): array
    {
        return [
            'count' => $this->plugins->all()->count(),
        ];
    }
}
