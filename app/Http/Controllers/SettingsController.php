<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateThemeRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Show the settings page for the authenticated user.
     */
    public function show(): View
    {
        return view('settings.show', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Save the authenticated user's appearance preference.
     */
    public function updateTheme(UpdateThemeRequest $request): RedirectResponse
    {
        $request->user()->update(['theme' => $request->validated('theme')]);

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
