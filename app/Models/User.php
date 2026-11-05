<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AdminModule;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'surname', 'email', 'password', 'theme', 'enabled_modules', 'module_order', 'module_sections', 'dashboard_hidden_widgets', 'email_notifications'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The model's default attribute values.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_active' => true,
    ];

    /**
     * The user's full name (name plus surname).
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => trim($this->name.' '.($this->surname ?? '')),
        );
    }

    /**
     * The keys of the admin modules enabled for the user.
     *
     * @return list<string>
     */
    public function enabledModules(): array
    {
        return array_map(
            fn (AdminModule $module): string => $module->value,
            AdminModule::enabledFor($this),
        );
    }

    /**
     * Whether the given admin module is enabled for the user.
     */
    public function hasModule(string $module): bool
    {
        return in_array($module, $this->enabledModules(), true);
    }

    /**
     * The identifiers of the dashboard widgets hidden by the user.
     *
     * @return list<string>
     */
    public function hiddenDashboardWidgets(): array
    {
        $hidden = $this->dashboard_hidden_widgets;

        if (! is_array($hidden)) {
            return [];
        }

        return array_values(array_filter(
            $hidden,
            fn (mixed $widget): bool => is_string($widget) && $widget !== '',
        ));
    }

    /**
     * Whether the given dashboard widget is hidden by the user.
     */
    public function hasHiddenDashboardWidget(string $widget): bool
    {
        return in_array($widget, $this->hiddenDashboardWidgets(), true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'enabled_modules' => 'array',
            'module_order' => 'array',
            'module_sections' => 'array',
            'dashboard_hidden_widgets' => 'array',
            'email_notifications' => 'boolean',
        ];
    }
}
