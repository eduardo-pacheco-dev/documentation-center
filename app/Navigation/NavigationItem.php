<?php

namespace App\Navigation;

use App\Enums\AdminModule;
use Closure;
use Illuminate\Http\Request;
use InvalidArgumentException;

/**
 * A single navigation entry contributed to the administration sidebar.
 *
 * Plugins build an item and register it in the NavigationRegistry from their
 * service provider, mirroring how the WidgetRegistry exposes dashboard blocks.
 */
class NavigationItem
{
    public const SECTION_SIDEBAR = AdminModule::SECTION_SIDEBAR;

    public const SECTION_MODULES = AdminModule::SECTION_MODULES;

    private string $icon = 'cube';

    private string $section = self::SECTION_MODULES;

    private ?string $module = null;

    private int $order = 100;

    private ?string $activePattern = null;

    /**
     * @var (Closure(Request): bool)|null
     */
    private ?Closure $visibility = null;

    private function __construct(
        private readonly string $id,
        private readonly string $label,
        private readonly string $route,
    ) {}

    /**
     * Start building a navigation item.
     *
     * The identifier must be stable and namespaced by the contributor
     * (e.g. "acme.reports"), because registering it again replaces the
     * previous item instead of duplicating it.
     */
    public static function make(string $id, string $label, string $route): self
    {
        return new self($id, $label, $route);
    }

    /**
     * The icon rendered next to the label.
     */
    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * The sidebar section that renders the item.
     */
    public function section(string $section): self
    {
        if (! in_array($section, [self::SECTION_SIDEBAR, self::SECTION_MODULES], true)) {
            throw new InvalidArgumentException("Invalid navigation section [{$section}].");
        }

        $this->section = $section;

        return $this;
    }

    /**
     * Render the item directly in the sidebar.
     */
    public function inSidebar(): self
    {
        return $this->section(self::SECTION_SIDEBAR);
    }

    /**
     * Render the item inside the "Módulos" dropdown.
     */
    public function inModules(): self
    {
        return $this->section(self::SECTION_MODULES);
    }

    /**
     * Only show the item when the user has the given admin module enabled.
     */
    public function module(?string $module): self
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Sort order inside the section; lower values render first.
     */
    public function order(int $order): self
    {
        $this->order = $order;

        return $this;
    }

    /**
     * The route pattern used to highlight the active item.
     *
     * Defaults to the route itself, expanding ".index" routes to their
     * siblings so detail pages keep the item highlighted.
     */
    public function active(string $pattern): self
    {
        $this->activePattern = $pattern;

        return $this;
    }

    /**
     * Register an extra visibility check for the current request.
     *
     * @param  Closure(Request): bool  $callback
     */
    public function visible(Closure $callback): self
    {
        $this->visibility = $callback;

        return $this;
    }

    /**
     * The stable identifier used to replace duplicate registrations.
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * The label shown in the sidebar.
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * The named route the item links to.
     */
    public function route(): string
    {
        return $this->route;
    }

    /**
     * The icon name understood by the icon component.
     */
    public function iconName(): string
    {
        return $this->icon;
    }

    /**
     * The section that renders the item.
     */
    public function sectionName(): string
    {
        return $this->section;
    }

    /**
     * The admin module required to see the item, when any.
     */
    public function moduleName(): ?string
    {
        return $this->module;
    }

    /**
     * The sort order inside the section.
     */
    public function sortOrder(): int
    {
        return $this->order;
    }

    /**
     * The route pattern used to highlight the active item.
     */
    public function activePattern(): string
    {
        if ($this->activePattern !== null) {
            return $this->activePattern;
        }

        return str_ends_with($this->route, '.index')
            ? str_replace('.index', '.*', $this->route)
            : $this->route;
    }

    /**
     * Whether the item should render for the given request.
     */
    public function isVisible(Request $request): bool
    {
        $user = $request->user();

        if ($this->module !== null && ($user === null || ! $user->hasModule($this->module))) {
            return false;
        }

        return $this->visibility === null || (bool) ($this->visibility)($request);
    }
}
