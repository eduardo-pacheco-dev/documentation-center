<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateModulesRequest;
use App\Http\Requests\UpdateNotificationSettingsRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateStorageSettingsRequest;
use App\Http\Requests\UpdateSystemTimezoneRequest;
use App\Http\Requests\UpdateThemeRequest;
use App\Models\StorageSetting;
use App\Navigation\NavigationCatalog;
use App\Navigation\NavigationEntry;
use App\Plugins\Plugin;
use App\Plugins\PluginRepository;
use App\Settings\SettingsSectionRegistry;
use App\Settings\StorageSettings;
use App\Settings\SystemSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Show the settings page for the authenticated user.
     */
    public function show(
        Request $request,
        PluginRepository $plugins,
        SettingsSectionRegistry $settingsSections,
        NavigationCatalog $navigation,
    ): View {
        $user = auth()->user();

        $installedPlugins = $user->is_admin ? $plugins->all() : collect();
        $storageSettings = $user->is_admin ? StorageSettings::current() : null;

        $navigationEntries = $navigation->entries($user, $request);

        return view('settings.show', [
            'user' => $user,
            'modules' => $navigation->toggleable($user, $request),
            'enabledModules' => $navigation->enabled($user, $request)
                ->map(fn (NavigationEntry $entry): string => $entry->key())
                ->all(),
            'moduleMeta' => $navigationEntries->mapWithKeys(
                fn (NavigationEntry $entry): array => [
                    $entry->key() => [
                        'label' => $entry->label(),
                        'group' => $navigation->sectionFor($user, $entry),
                    ],
                ],
            )->all(),
            'plugins' => $installedPlugins,
            'systemTimezone' => SystemSettings::timezone(),
            'timezoneGroups' => SystemSettings::groupedIdentifiers(),
            'storageSettings' => $storageSettings,
            'storageConfigured' => $storageSettings === null
                ? []
                : collect(StorageSetting::providers())
                    ->mapWithKeys(fn (string $provider): array => [
                        $provider => StorageSettings::configured($provider, $storageSettings),
                    ])
                    ->all(),
            'pluginSettingsSections' => $settingsSections->forRequest($request),
            'pluginStats' => [
                'total' => $installedPlugins->count(),
                'enabled' => $installedPlugins->filter(fn (Plugin $plugin): bool => $plugin->enabled())->count(),
                'disabled' => $installedPlugins->filter(fn (Plugin $plugin): bool => $plugin->disabled())->count(),
            ],
        ]);
    }

    /**
     * Save the modules enabled for the authenticated user.
     */
    public function updateModules(UpdateModulesRequest $request): RedirectResponse
    {
        $user = $request->user();
        $navigation = app(NavigationCatalog::class);
        $toggleable = $navigation->toggleable($user, $request);
        $submitted = collect($request->validated('modules') ?? []);

        $data = [
            'enabled_modules' => $toggleable
                ->reject(fn (NavigationEntry $entry): bool => $entry->isPlugin())
                ->filter(fn (NavigationEntry $entry): bool => $submitted->contains($entry->key()))
                ->map(fn (NavigationEntry $entry): string => $entry->key())
                ->values()
                ->all(),
            'disabled_modules' => $toggleable
                ->filter(fn (NavigationEntry $entry): bool => $entry->isPlugin())
                ->reject(fn (NavigationEntry $entry): bool => $submitted->contains($entry->key()))
                ->map(fn (NavigationEntry $entry): string => $entry->key())
                ->values()
                ->all(),
        ];

        $available = $navigation->entries($user, $request)
            ->map(fn (NavigationEntry $entry): string => $entry->key())
            ->all();

        $order = collect($request->validated('order') ?? [])
            ->intersect($available)
            ->values()
            ->all();

        if ($order !== []) {
            $data['module_order'] = $order;
        }

        $sections = collect($request->validated('sections') ?? [])
            ->only($available)
            ->all();

        if ($sections !== []) {
            $data['module_sections'] = $sections;
        }

        $user->update($data);

        return back()->with('status', 'Módulos atualizados com sucesso.');
    }

    /**
     * Save the authenticated user's appearance preference.
     */
    public function updateTheme(UpdateThemeRequest $request): RedirectResponse
    {
        $theme = $request->validated('theme');

        $request->user()->update(['theme' => $theme]);

        cookie()->queue('theme', $theme, 60 * 24 * 365);

        return back()->with('status', 'Preferência de tema salva com sucesso.');
    }

    /**
     * Save the authenticated user's notification preferences.
     */
    public function updateNotifications(UpdateNotificationSettingsRequest $request): RedirectResponse
    {
        $request->user()->update([
            'email_notifications' => $request->boolean('email_notifications'),
        ]);

        return back()->with('status', 'Preferências de notificação atualizadas com sucesso.');
    }

    /**
     * Save the system timezone used across the application.
     */
    public function updateTimezone(UpdateSystemTimezoneRequest $request): RedirectResponse
    {
        SystemSettings::setTimezone($request->validated('timezone'));

        return back()->with('status', 'Fuso horário do sistema atualizado com sucesso.');
    }

    /**
     * Save the storage provider used by the plugins.
     */
    public function updateStorage(UpdateStorageSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        foreach (['aws_secret', 'azure_account_key', 'cloudflare_secret_access_key'] as $secret) {
            if (blank($data[$secret] ?? null)) {
                unset($data[$secret]);
            }
        }

        StorageSettings::save($data);

        return redirect()
            ->to(route('settings').'#armazenamento')
            ->with('status', 'Configurações de armazenamento salvas com sucesso.');
    }

    /**
     * Update the authenticated user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update(['password' => $request->validated('password')]);

        return back()->with('status', 'Senha atualizada com sucesso.');
    }

    /**
     * Delete the authenticated user's account and sign them out.
     */
    public function destroy(DeleteAccountRequest $request): RedirectResponse
    {
        $user = $request->user();

        auth()->guard('web')->logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Sua conta foi excluída com sucesso.');
    }
}
