<?php

namespace App\Http\Controllers;

use App\Enums\AdminModule;
use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateModulesRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateThemeRequest;
use App\Plugins\Plugin;
use App\Plugins\PluginRepository;
use App\Settings\SettingsSectionRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Show the settings page for the authenticated user.
     */
    public function show(Request $request, PluginRepository $plugins, SettingsSectionRegistry $settingsSections): View
    {
        $user = auth()->user();

        $installedPlugins = $user->is_admin ? $plugins->all() : collect();

        return view('settings.show', [
            'user' => $user,
            'modules' => AdminModule::toggleableFor($user),
            'orderModules' => AdminModule::sortForUser($user, AdminModule::availableFor($user)),
            'enabledModules' => array_map(
                fn (AdminModule $module): string => $module->value,
                AdminModule::enabledFor($user),
            ),
            'plugins' => $installedPlugins,
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
        $data = [
            'enabled_modules' => array_values($request->validated('modules') ?? []),
        ];

        $order = array_values($request->validated('order') ?? []);

        if ($order !== []) {
            $data['module_order'] = $order;
        }

        $sections = collect($request->validated('sections') ?? [])
            ->only(array_map(
                fn (AdminModule $module): string => $module->value,
                AdminModule::availableFor($request->user()),
            ))
            ->all();

        if ($sections !== []) {
            $data['module_sections'] = $sections;
        }

        $request->user()->update($data);

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
