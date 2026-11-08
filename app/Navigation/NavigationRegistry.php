<?php

namespace App\Navigation;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Holds every navigation item contributed to the administration sidebar.
 *
 * Items are keyed by their stable identifier, so registering the same item
 * twice replaces the previous instance instead of duplicating it.
 */
class NavigationRegistry
{
    /**
     * @var array<string, NavigationItem>
     */
    private array $items = [];

    /**
     * Register an item, replacing any item with the same identifier.
     */
    public function register(NavigationItem $item): self
    {
        $this->items[$item->id()] = $item;

        return $this;
    }

    /**
     * Register several items at once.
     *
     * @param  iterable<NavigationItem>  $items
     */
    public function registerMany(iterable $items): self
    {
        foreach ($items as $item) {
            $this->register($item);
        }

        return $this;
    }

    /**
     * Remove an item from the registry.
     */
    public function forget(string $id): self
    {
        unset($this->items[$id]);

        return $this;
    }

    /**
     * Determine whether an item is registered.
     */
    public function has(string $id): bool
    {
        return isset($this->items[$id]);
    }

    /**
     * Find a registered item by identifier.
     */
    public function get(string $id): ?NavigationItem
    {
        return $this->items[$id] ?? null;
    }

    /**
     * Every registered item, ordered for display.
     *
     * @return Collection<int, NavigationItem>
     */
    public function all(): Collection
    {
        return collect($this->items)
            ->sortBy(fn (NavigationItem $item): array => [$item->sortOrder(), $item->id()])
            ->values();
    }

    /**
     * The items that should render for the given request.
     *
     * @return Collection<int, NavigationItem>
     */
    public function forRequest(Request $request): Collection
    {
        return $this->all()
            ->filter(fn (NavigationItem $item): bool => $item->isVisible($request))
            ->values();
    }

    /**
     * The admin module keys declared by the registered items.
     *
     * @return list<string>
     */
    public function modules(): array
    {
        return collect($this->items)
            ->map(fn (NavigationItem $item): ?string => $item->moduleName())
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
