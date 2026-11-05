<?php

namespace App\Dashboard;

use Illuminate\Http\Request;

/**
 * A single block rendered on the administration dashboard.
 *
 * Core widgets ship with the application and plugins contribute their own by
 * extending this class and registering the instance in the WidgetRegistry.
 */
abstract class Widget
{
    /**
     * Stable identifier, namespaced by the contributor (e.g. "core.users").
     */
    abstract public function id(): string;

    /**
     * Human readable title used by the widget shell and for debugging.
     */
    abstract public function title(): string;

    /**
     * A short description used by the dashboard editor.
     */
    public function description(): ?string
    {
        return null;
    }

    /**
     * The icon used by the dashboard editor.
     */
    public function icon(): ?string
    {
        return null;
    }

    /**
     * The Blade view rendered for the widget.
     */
    abstract public function view(): string;

    /**
     * Data passed to the widget view.
     *
     * @return array<string, mixed>
     */
    public function data(Request $request): array
    {
        return [];
    }

    /**
     * Sort order in the dashboard grid; lower values render first.
     */
    public function order(): int
    {
        return 0;
    }

    /**
     * How many grid columns the widget occupies, from 1 to 3.
     */
    public function columnSpan(): int
    {
        return 1;
    }

    /**
     * Whether the widget should render for the current request.
     *
     * Use this hook to hide widgets from users without the required profile,
     * permission or module access.
     */
    public function visible(Request $request): bool
    {
        return true;
    }
}
