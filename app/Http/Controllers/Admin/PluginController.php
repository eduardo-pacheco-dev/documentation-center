<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UploadPluginRequest;
use App\Plugins\Exceptions\PluginInstallException;
use App\Plugins\Plugin;
use App\Plugins\PluginInstaller;
use App\Plugins\PluginRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PluginController extends Controller
{
    public function __construct(
        private readonly PluginRepository $plugins,
        private readonly PluginInstaller $installer,
    ) {}

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
     * Install a plugin from an uploaded ZIP archive.
     */
    public function store(UploadPluginRequest $request): RedirectResponse
    {
        try {
            $plugin = $this->installer->install((string) $request->file('file')?->getRealPath());
        } catch (PluginInstallException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        return back()->with(
            'status',
            sprintf(
                'Plugin %s instalado com sucesso. Habilite-o para carregar seus providers e rotas.',
                $plugin->getName(),
            ),
        );
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
