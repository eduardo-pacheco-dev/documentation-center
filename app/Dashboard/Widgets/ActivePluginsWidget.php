<?php

namespace App\Dashboard\Widgets;

use App\Dashboard\Widget;
use App\Plugins\PluginRepository;
use Illuminate\Http\Request;

/**
 * Number of plugins currently enabled in the application.
 */
class ActivePluginsWidget extends Widget
{
    public function __construct(private readonly PluginRepository $plugins) {}

    public function id(): string
    {
        return 'core.plugins.active';
    }

    public function title(): string
    {
        return 'Plugins ativos';
    }

    public function description(): ?string
    {
        return 'Plugins habilitados e em execução.';
    }

    public function icon(): ?string
    {
        return 'chart-bar';
    }

    public function view(): string
    {
        return 'admin.widgets.active-plugins';
    }

    public function order(): int
    {
        return 30;
    }

    public function data(Request $request): array
    {
        return [
            'count' => $this->plugins->enabled()->count(),
        ];
    }
}
