<?php

namespace App\Settings;

use Illuminate\Http\Request;

/**
 * A section contributed by a plugin to the settings page.
 */
abstract class SettingsSection
{
    /**
     * Stable identifier used as the section anchor.
     */
    abstract public function id(): string;

    /**
     * Human readable title shown in the navigation and section content.
     */
    abstract public function title(): string;

    /**
     * Short description rendered in the section header.
     */
    abstract public function description(): string;

    /**
     * Icon name from the icon component.
     */
    abstract public function icon(): string;

    /**
     * The Blade view rendered for the section.
     */
    abstract public function view(): string;

    /**
     * Sort order in the settings navigation.
     */
    public function order(): int
    {
        return 50;
    }

    /**
     * Whether the section should render for the current request.
     */
    public function visible(Request $request): bool
    {
        return true;
    }

    /**
     * Data passed to the section view.
     *
     * @return array<string, mixed>
     */
    public function data(Request $request): array
    {
        return [];
    }
}
