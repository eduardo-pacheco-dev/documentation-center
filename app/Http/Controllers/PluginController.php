<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadPluginRequest;
use App\Plugins\Exceptions\PluginDependencyException;
use App\Plugins\Exceptions\PluginInstallException;
use App\Plugins\Exceptions\PluginRemovalException;
use App\Plugins\PluginInstaller;
use App\Plugins\PluginMigrator;
use App\Plugins\PluginRemover;
use App\Plugins\PluginRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class PluginController extends Controller
{
    public function __construct(
        private readonly PluginRepository $plugins,
        private readonly PluginInstaller $installer,
        private readonly PluginMigrator $migrator,
        private readonly PluginRemover $remover,
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

        try {
            if ($validated['enabled']) {
                if ($plugin->disabled()) {
                    $plugin->enable();

                    try {
                        $this->migrator->migrate($plugin);
                        $plugin->install();
                    } catch (\Throwable $exception) {
                        $plugin->disable();
                        $this->plugins->clear();
                        $this->clearRouteCache();

                        return back()->withErrors([
                            'plugin' => sprintf('Não foi possível habilitar %s: %s', $plugin->getName(), $exception->getMessage()),
                        ]);
                    }
                }
            } else {
                $plugin->disable();
            }
        } catch (PluginDependencyException $exception) {
            return back()->withErrors(['plugin' => $exception->getMessage()]);
        }

        $this->plugins->clear();
        $this->clearRouteCache();

        return back()->with(
            'status',
            sprintf(
                'Plugin %s %s. Reinicie os workers de fila para aplicar a mudança no processamento em background.',
                $plugin->getName(),
                $validated['enabled'] ? 'habilitado' : 'desabilitado',
            ),
        );
    }

    /**
     * Remove the given plugin and delete its files.
     */
    public function destroy(string $alias): RedirectResponse
    {
        $plugin = $this->plugins->findOrFail($alias);

        try {
            $this->remover->remove($plugin->getAlias());
        } catch (PluginRemovalException|PluginDependencyException $exception) {
            return back()->withErrors(['plugin' => $exception->getMessage()]);
        }

        return back()->with('status', sprintf('Plugin %s removido.', $plugin->getName()));
    }

    /**
     * Drop the cached routes so the new plugin route set is used.
     */
    private function clearRouteCache(): void
    {
        Artisan::call('route:clear');
    }
}
