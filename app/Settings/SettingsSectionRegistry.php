<?php

namespace App\Settings;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Holds the settings sections contributed by plugins.
 */
class SettingsSectionRegistry
{
    /**
     * @var array<string, SettingsSection>
     */
    private array $sections = [];

    /**
     * Register a section, replacing any section with the same identifier.
     */
    public function register(SettingsSection $section): self
    {
        $this->sections[$section->id()] = $section;

        return $this;
    }

    /**
     * Register several sections at once.
     *
     * @param  iterable<SettingsSection>  $sections
     */
    public function registerMany(iterable $sections): self
    {
        foreach ($sections as $section) {
            $this->register($section);
        }

        return $this;
    }

    /**
     * Every registered section, ordered for display.
     *
     * @return Collection<int, SettingsSection>
     */
    public function all(): Collection
    {
        return collect($this->sections)
            ->sortBy(fn (SettingsSection $section): array => [$section->order(), $section->id()])
            ->values();
    }

    /**
     * The sections that should render for the given request.
     *
     * @return Collection<int, SettingsSection>
     */
    public function forRequest(Request $request): Collection
    {
        return $this->all()
            ->filter(fn (SettingsSection $section): bool => $section->visible($request))
            ->values();
    }
}
