<?php

namespace App\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Holds every widget available to the administration dashboard.
 *
 * Widgets are keyed by their stable identifier, so registering a widget twice
 * replaces the previous instance instead of duplicating it.
 */
class WidgetRegistry
{
    /**
     * @var array<string, Widget>
     */
    private array $widgets = [];

    /**
     * Register a widget, replacing any widget with the same identifier.
     */
    public function register(Widget $widget): self
    {
        $this->widgets[$widget->id()] = $widget;

        return $this;
    }

    /**
     * Register several widgets at once.
     *
     * @param  iterable<Widget>  $widgets
     */
    public function registerMany(iterable $widgets): self
    {
        foreach ($widgets as $widget) {
            $this->register($widget);
        }

        return $this;
    }

    /**
     * Remove a widget from the registry.
     */
    public function forget(string $id): self
    {
        unset($this->widgets[$id]);

        return $this;
    }

    /**
     * Determine whether a widget is registered.
     */
    public function has(string $id): bool
    {
        return isset($this->widgets[$id]);
    }

    /**
     * Find a registered widget by identifier.
     */
    public function get(string $id): ?Widget
    {
        return $this->widgets[$id] ?? null;
    }

    /**
     * Every registered widget, ordered for display.
     *
     * @return Collection<int, Widget>
     */
    public function all(): Collection
    {
        return collect($this->widgets)
            ->sortBy(fn (Widget $widget): array => [$widget->order(), $widget->id()])
            ->values();
    }

    /**
     * The widgets that should render for the given request.
     *
     * @return Collection<int, Widget>
     */
    public function forRequest(Request $request): Collection
    {
        return $this->all()
            ->filter(fn (Widget $widget): bool => $widget->visible($request))
            ->values();
    }
}
