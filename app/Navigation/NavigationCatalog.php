<?php

namespace App\Navigation;

use App\Enums\AdminModule;
use App\Models\User;
use App\Plugins\PluginRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/**
 * Merges the core administration modules with the buttons contributed by
 * plugins, exposing a single list to the sidebar and the settings page.
 *
 * The user preferences stored on the user model (enabled_modules,
 * disabled_modules, module_order and module_sections) are resolved here so
 * core modules and plugin buttons follow the same rules everywhere.
 */
class NavigationCatalog
{
    public function __construct(
        private readonly NavigationRegistry $registry,
        private readonly PluginRepository $plugins,
    ) {}

    /**
     * Every entry available to the user, ordered for display.
     *
     * @return Collection<int, NavigationEntry>
     */
    public function entries(User $user, Request $request): Collection
    {
        $claimedModules = array_values(array_unique([
            ...$this->plugins->modules(),
            ...$this->registry->modules(),
        ]));

        $core = collect(AdminModule::availableFor($user))
            ->reject(fn (AdminModule $module): bool => in_array($module->value, $claimedModules, true))
            ->map(fn (AdminModule $module): NavigationEntry => NavigationEntry::fromModule($module));

        $registered = $this->registry->forRequest($request)
            ->filter(fn (NavigationItem $item): bool => Route::has($item->route()))
            ->map(fn (NavigationItem $item): NavigationEntry => NavigationEntry::fromNavigationItem($item));

        $registeredRoutes = $registered
            ->map(fn (NavigationEntry $entry): string => $entry->route())
            ->all();

        $manifest = collect($this->plugins->navigation())
            ->filter(fn (array $item): bool => Route::has($item['route']))
            ->filter(fn (array $item): bool => $item['module'] === null || $user->hasModule($item['module']))
            ->reject(fn (array $item): bool => in_array($item['route'], $registeredRoutes, true))
            ->values()
            ->map(fn (array $item, int $index): NavigationEntry => NavigationEntry::fromManifestItem($item, $index));

        return $this->sortForUser($user, $core->concat($registered)->concat($manifest));
    }

    /**
     * The entries the user can enable or disable in the settings page.
     *
     * @return Collection<int, NavigationEntry>
     */
    public function toggleable(User $user, Request $request): Collection
    {
        return $this->entries($user, $request)
            ->filter(fn (NavigationEntry $entry): bool => $entry->isToggleable())
            ->values();
    }

    /**
     * The entries the user has enabled.
     *
     * @return Collection<int, NavigationEntry>
     */
    public function enabled(User $user, Request $request): Collection
    {
        return $this->entries($user, $request)
            ->filter(fn (NavigationEntry $entry): bool => $this->isEnabled($user, $entry))
            ->values();
    }

    /**
     * Whether the entry is enabled for the given user.
     *
     * Core modules keep their historical semantics: a missing preference
     * enables everything and the dashboard is always enabled. Plugin buttons
     * use an opt-out list, so a button added later is enabled by default until
     * the user turns it off.
     */
    public function isEnabled(User $user, NavigationEntry $entry): bool
    {
        if (! $entry->isPlugin()) {
            if ($entry->key() === AdminModule::Dashboard->value) {
                return true;
            }

            $enabled = $user->enabled_modules;

            return ! is_array($enabled) || in_array($entry->key(), $enabled, true);
        }

        $disabled = $user->disabled_modules;

        return ! is_array($disabled) || ! in_array($entry->key(), $disabled, true);
    }

    /**
     * The section chosen by the user for the given entry.
     *
     * @return AdminModule::SECTION_*
     */
    public function sectionFor(User $user, NavigationEntry $entry): string
    {
        $sections = $user->module_sections;
        $section = is_array($sections) ? ($sections[$entry->key()] ?? null) : null;

        if (in_array($section, [AdminModule::SECTION_SIDEBAR, AdminModule::SECTION_MODULES], true)) {
            return $section;
        }

        return $entry->defaultSection();
    }

    /**
     * Sort the entries using the order chosen by the user.
     *
     * @param  Collection<int, NavigationEntry>  $entries
     * @return Collection<int, NavigationEntry>
     */
    private function sortForUser(User $user, Collection $entries): Collection
    {
        $order = $user->module_order;

        if (! is_array($order) || $order === []) {
            return $entries->values();
        }

        $positions = array_flip($order);

        return $entries
            ->sortBy(fn (NavigationEntry $entry): array => [
                $positions[$entry->key()] ?? PHP_INT_MAX,
                $entry->order(),
                $entry->key(),
            ])
            ->values();
    }
}
