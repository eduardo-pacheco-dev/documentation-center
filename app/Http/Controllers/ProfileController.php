<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show the authenticated user's profile.
     */
    public function show(): View
    {
        return view('profile.show', [
            'user' => auth()->user(),
        ]);
    }

    /**
     * Update the authenticated user's name and e-mail.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->safe()->only(['name', 'email']));

        return back()->with('status', 'Perfil atualizado com sucesso.');
    }
}
