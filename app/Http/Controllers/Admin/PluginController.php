<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Plugins\Plugin;
use App\Plugins\PluginRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PluginController extends Controller
{
    public function __construct(private readonly PluginRepository $plugins) {}

    /**
     * List every discovered plugin and its status.
     */
    public function index(): View
    {
        $plugins = $this->plugins->all();

        return view('admin.plugins.index', [
            'plugins' => $plugins,
            'stats' => [
                'total' => $plugins->count(),
                'enabled' => $plugins->filter(fn (Plugin $plugin): bool => $plugin->enabled())->count(),
                'disabled' => $plugins->filter(fn (Plugin $plugin): bool => $plugin->disabled())->count(),
            ],
        ]);
    }

    /**
     * Enable or disable the given plugin.
     */
    public function update(Request $request, string $alias): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $plugin = $this->plugins->findOrFail($alias);

        if ($validated['enabled']) {
            $plugin->enable();
        } else {
            $plugin->disable();
        }

        $this->plugins->clear();

        return back()->with(
            'status',
            sprintf(
                'Plugin %s %s. Reinicie os workers de fila para aplicar a mudança no processamento em background.',
                $plugin->getName(),
                $validated['enabled'] ? 'habilitado' : 'desabilitado',
            ),
        );
    }
}
