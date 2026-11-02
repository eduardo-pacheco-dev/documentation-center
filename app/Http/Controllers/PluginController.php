<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadPluginRequest;
use App\Plugins\Exceptions\PluginInstallException;
use App\Plugins\PluginInstaller;
use App\Plugins\PluginRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PluginController extends Controller
{
    public function __construct(
        private readonly PluginRepository $plugins,
        private readonly PluginInstaller $installer,
    ) {}

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
