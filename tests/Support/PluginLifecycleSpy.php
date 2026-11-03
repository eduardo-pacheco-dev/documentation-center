<?php

namespace Tests\Support;

use App\Plugins\Contracts\PluginLifecycle;
use RuntimeException;

/**
 * Records the lifecycle hooks invoked by the plugin system during tests.
 */
class PluginLifecycleSpy implements PluginLifecycle
{
    /**
     * @var list<string>
     */
    public static array $calls = [];

    public static bool $failInstall = false;

    public static bool $failUninstall = false;

    public function install(): void
    {
        static::$calls[] = 'install';

        if (static::$failInstall) {
            throw new RuntimeException('The install hook failed.');
        }
    }

    public function uninstall(): void
    {
        static::$calls[] = 'uninstall';

        if (static::$failUninstall) {
            throw new RuntimeException('The uninstall hook failed.');
        }
    }

    public static function reset(): void
    {
        static::$calls = [];
        static::$failInstall = false;
        static::$failUninstall = false;
    }
}
