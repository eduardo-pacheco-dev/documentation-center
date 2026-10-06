<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * List every registered user.
     */
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::latest('created_at')->paginate(10),
        ]);
    }

    /**
     * Create a new user from the administration panel.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->only(['name', 'email', 'password']));
        $user->is_admin = $request->boolean('is_admin');
        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'Usuário criado com sucesso.');
    }

    /**
     * Remove a registered user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'Você não pode excluir a própria conta.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', 'Usuário excluído com sucesso.');
    }
}
