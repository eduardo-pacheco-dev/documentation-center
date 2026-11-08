<?php

namespace App\Navigation;

use App\Enums\AdminModule;

/**
 * A navigation entry normalised from a core module or a plugin contribution.
 *
 * The settings page, the sidebar and the modules menu consume the same list,
 * so ordering, visibility and section preferences work identically for core
 * modules and plugin buttons.
 */
final class NavigationEntry
{
    public function __construct(
        private readonly string $key,
        private readonly string $label,
        private readonly string $icon,
        private readonly string $section,
        private readonly string $route,
        private readonly string $activePattern,
        private readonly ?string $module = null,
        private readonly int $order = 0,
        private readonly bool $toggleable = true,
        private readonly bool $plugin = false,
    ) {}

    /**
     * Build the entry for a core administration module.
     */
    public static function fromModule(AdminModule $module): self
    {
        return new self(
            key: $module->value,
            label: $module->label(),
            icon: $module->icon(),
            section: $module->isPrimary() ? AdminModule::SECTION_SIDEBAR : AdminModule::SECTION_MODULES,
            route: $module->routeName(),
            activePattern: $module->activePattern(),
            toggleable: $module !== AdminModule::Dashboard,
        );
    }

    /**
     * Build the entry for a button registered by a plugin provider.
     */
    public static function fromNavigationItem(NavigationItem $item): self
    {
        return new self(
            key: $item->id(),
            label: $item->label(),
            icon: $item->iconName(),
            section: $item->sectionName(),
            route: $item->route(),
            activePattern: $item->activePattern(),
            module: $item->moduleName(),
            order: $item->sortOrder(),
            plugin: true,
        );
    }

    /**
     * Build the entry for a button declared in the plugin manifest.
     *
     * @param  array{label: string, route: string, icon: string, active: string, primary: bool, module: ?string}  $item
     */
    public static function fromManifestItem(array $item, int $index): self
    {
        return new self(
            key: $item['route'],
            label: $item['label'],
            icon: $item['icon'],
            section: $item['primary'] ? AdminModule::SECTION_SIDEBAR : AdminModule::SECTION_MODULES,
            route: $item['route'],
            activePattern: $item['active'],
            module: $item['module'],
            order: 100 + $index,
            plugin: true,
        );
    }

    /**
     * The stable identifier used by the user preferences.
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * The label shown in the settings page and the navigation.
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * The icon name understood by the icon component.
     */
    public function icon(): string
    {
        return $this->icon;
    }

    /**
     * The default section when the user has no preference.
     */
    public function defaultSection(): string
    {
        return $this->section;
    }

    /**
     * The named route the entry links to.
     */
    public function route(): string
    {
        return $this->route;
    }

    /**
     * The route pattern used to highlight the active entry.
     */
    public function activePattern(): string
    {
        return $this->activePattern;
    }

    /**
     * The admin module required to see the entry, when any.
     */
    public function module(): ?string
    {
        return $this->module;
    }

    /**
     * The default sort order inside the section.
     */
    public function order(): int
    {
        return $this->order;
    }

    /**
     * Whether the user can enable or disable the entry in the settings.
     */
    public function isToggleable(): bool
    {
        return $this->toggleable;
    }

    /**
     * Whether the entry was contributed by a plugin.
     */
    public function isPlugin(): bool
    {
        return $this->plugin;
    }
}
