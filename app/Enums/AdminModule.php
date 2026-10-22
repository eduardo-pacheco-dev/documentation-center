<?php

namespace App\Enums;

use App\Models\User;

enum AdminModule: string
{
    case Dashboard = 'dashboard';
    case Projects = 'projects';
    case WorkOrders = 'work-orders';
    case Clients = 'clients';
    case Files = 'files';
    case Agenda = 'agenda';
    case Tasks = 'tasks';
    case Forms = 'forms';
    case Erbs = 'erbs';
    case RadioLinks = 'radio-links';
    case Catalog = 'catalog';
    case Equipments = 'equipments';
    case Links = 'links';
    case Colaboradores = 'colaboradores';
    case FormTemplates = 'form-templates';
    case Backups = 'backups';
    case Users = 'users';

    /**
     * The label shown in the navigation and settings.
     */
    public function label(): string
    {
        return match ($this) {
            self::Dashboard => 'Início',
            self::Projects => 'Projetos',
            self::WorkOrders => 'Ordens de serviço',
            self::Clients => 'Clientes',
            self::Files => 'Arquivos',
            self::Agenda => 'Agenda',
            self::Tasks => 'Tarefas',
            self::Forms => 'Formulários',
            self::Erbs => 'ERBs',
            self::RadioLinks => 'Radio links',
            self::Catalog => 'Catálogo',
            self::Equipments => 'Equipamentos',
            self::Links => 'Links',
            self::Colaboradores => 'Colaboradores',
            self::FormTemplates => 'Templates',
            self::Backups => 'Backups',
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
            self::Projects => 'briefcase',
            self::WorkOrders => 'clipboard-document-list',
            self::Clients => 'building-office',
            self::Files => 'document-text',
            self::Agenda => 'calendar-days',
            self::Tasks => 'check-circle',
            self::Forms => 'document-check',
            self::Erbs => 'signal',
            self::RadioLinks => 'arrows-right-left',
            self::Catalog => 'cube',
            self::Equipments => 'wrench-screwdriver',
            self::Links => 'link',
            self::Colaboradores => 'user',
            self::FormTemplates => 'document-duplicate',
            self::Backups => 'rectangle-stack',
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
            self::Projects => 'admin.projects.index',
            self::WorkOrders => 'admin.work-orders.index',
            self::Clients => 'admin.clients.index',
            self::Files => 'admin.files.index',
            self::Agenda => 'admin.agenda.index',
            self::Tasks => 'admin.tasks.index',
            self::Forms => 'admin.forms.index',
            self::Erbs => 'admin.erbs.index',
            self::RadioLinks => 'admin.radio-links.index',
            self::Catalog => 'admin.catalog.index',
            self::Equipments => 'admin.equipments.index',
            self::Links => 'admin.links.index',
            self::Colaboradores => 'admin.colaboradores.index',
            self::FormTemplates => 'admin.form-templates.index',
            self::Backups => 'admin.backups.index',
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
        return match ($this) {
            self::Dashboard,
            self::Projects,
            self::WorkOrders,
            self::Clients,
            self::Files,
            self::Agenda,
            self::Tasks,
            self::Forms => true,
            default => false,
        };
    }

    /**
     * Whether only administrators can see the module.
     */
    public function isAdminOnly(): bool
    {
        return match ($this) {
            self::Backups, self::Users => true,
            default => false,
        };
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

        return array_values(array_filter(
            self::availableFor($user),
            fn (self $module): bool => $module === self::Dashboard
                || ! is_array($stored)
                || in_array($module->value, $stored, true),
        ));
    }

    /**
     * The modules the user can enable or disable in the settings.
     *
     * @return list<self>
     */
    public static function toggleableFor(User $user): array
    {
        return array_values(array_filter(
            self::availableFor($user),
            fn (self $module): bool => $module !== self::Dashboard,
        ));
    }
}
