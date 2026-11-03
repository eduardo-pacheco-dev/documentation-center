<?php

namespace App\Enums;

use App\Models\User;

enum AdminModule: string
{
    public const SECTION_SIDEBAR = 'sidebar';

    public const SECTION_MODULES = 'modules';

    case Dashboard = 'dashboard';
    case Users = 'users';

    /**
     * The label shown in the navigation and settings.
     */
    public function label(): string
    {
        return match ($this) {
            self::Dashboard => 'Início',
            self::Users => 'Usuários',
        };
    }

    /**
     * The icon component used in the navigation.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Dashboard => 'home',
            self::Users => 'users',
        };
    }

    /**
     * The named route that opens the module.
     */
    public function routeName(): string
    {
        return match ($this) {
            self::Dashboard => 'admin.dashboard',
            self::Users => 'admin.users.index',
        };
    }

    /**
     * The route pattern used to highlight the active navigation item.
     */
    public function activePattern(): string
    {
        return str_ends_with($this->routeName(), '.index')
            ? str_replace('.index', '.*', $this->routeName())
            : $this->routeName();
    }

    /**
     * Whether the module is shown directly in the sidebar.
     */
    public function isPrimary(): bool
    {
        return $this === self::Dashboard;
    }

    /**
     * Whether only administrators can see the module.
     */
    public function isAdminOnly(): bool
    {
        return $this === self::Users;
    }

    /**
     * The modules available to the given user.
     *
     * @return list<self>
     */
    public static function availableFor(User $user): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $module): bool => ! $module->isAdminOnly() || $user->is_admin,
        ));
    }

    /**
     * The modules enabled for the given user.
     *
     * A null preference means every available module is enabled. The dashboard
     * is always kept enabled so the user can reach the settings.
     *
     * @return list<self>
     */
    public static function enabledFor(User $user): array
    {
        $stored = $user->enabled_modules;

        $modules = array_values(array_filter(
            self::availableFor($user),
            fn (self $module): bool => $module === self::Dashboard
                || ! is_array($stored)
                || in_array($module->value, $stored, true),
        ));

        return self::sortForUser($user, $modules);
    }

    /**
     * The modules the user can enable or disable in the settings.
     *
     * @return list<self>
     */
    public static function toggleableFor(User $user): array
    {
        $modules = array_values(array_filter(
            self::availableFor($user),
            fn (self $module): bool => $module !== self::Dashboard,
        ));

        return self::sortForUser($user, $modules);
    }

    /**
     * The navigation section chosen by the user for the given module.
     *
     * @return self::SECTION_*
     */
    public static function sectionFor(User $user, self $module): string
    {
        $sections = $user->module_sections;
        $section = is_array($sections) ? ($sections[$module->value] ?? null) : null;

        if (in_array($section, [self::SECTION_SIDEBAR, self::SECTION_MODULES], true)) {
            return $section;
        }

        return $module->isPrimary() ? self::SECTION_SIDEBAR : self::SECTION_MODULES;
    }

    /**
     * Sort the modules using the order chosen by the user.
     *
     * @param  list<self>  $modules
     * @return list<self>
     */
    public static function sortForUser(User $user, array $modules): array
    {
        $order = $user->module_order;

        if (! is_array($order) || $order === []) {
            return array_values($modules);
        }

        $positions = array_flip($order);

        usort(
            $modules,
            fn (self $left, self $right): int => ($positions[$left->value] ?? PHP_INT_MAX)
                <=> ($positions[$right->value] ?? PHP_INT_MAX),
        );

        return array_values($modules);
    }
}
