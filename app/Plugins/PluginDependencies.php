<?php

namespace App\Plugins;

use App\Plugins\Exceptions\PluginDependencyException;

/**
 * Resolves the "requires" graph declared by the plugins' manifests.
 *
 * Dependencies must be installed and enabled before a plugin can be enabled,
 * and a plugin with enabled dependents cannot be disabled or removed.
 */
class PluginDependencies
{
    public function __construct(
        private readonly PluginRepository $plugins,
    ) {}

    /**
     * The declared dependencies that are missing or disabled.
     *
     * @return list<string>
     */
    public function missing(Plugin $plugin): array
    {
        return array_values(array_filter(
            $plugin->requires(),
            function (string $alias) use ($plugin): bool {
                $dependency = $this->plugins->get($alias);

                return $alias === $plugin->getAlias()
                    || $dependency === null
                    || $dependency->disabled();
            },
        ));
    }

    /**
     * The enabled plugins that declare the given plugin as a dependency.
     *
     * @return list<string>
     */
    public function dependents(Plugin $plugin): array
    {
        return $this->plugins->enabled()
            ->filter(fn (Plugin $candidate): bool => $candidate->getAlias() !== $plugin->getAlias()
                && in_array($plugin->getAlias(), $candidate->requires(), true))
            ->map(fn (Plugin $candidate): string => $candidate->getAlias())
            ->values()
            ->all();
    }

    /**
     * Ensure the plugin can be enabled.
     *
     * @throws PluginDependencyException
     */
    public function assertCanEnable(Plugin $plugin): void
    {
        $missing = $this->missing($plugin);

        if ($missing !== []) {
            throw PluginDependencyException::missing($plugin->getAlias(), $missing);
        }
    }

    /**
     * Ensure the plugin can be disabled.
     *
     * @throws PluginDependencyException
     */
    public function assertCanDisable(Plugin $plugin): void
    {
        $dependents = $this->dependents($plugin);

        if ($dependents !== []) {
            throw PluginDependencyException::requiredByDisabled($plugin->getAlias(), $dependents);
        }
    }

    /**
     * Ensure the plugin can be removed.
     *
     * @throws PluginDependencyException
     */
    public function assertCanRemove(Plugin $plugin): void
    {
        $dependents = $this->dependents($plugin);

        if ($dependents !== []) {
            throw PluginDependencyException::requiredByRemoved($plugin->getAlias(), $dependents);
        }
    }
}
