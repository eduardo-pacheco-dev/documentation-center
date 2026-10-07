<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
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
     * Update a registered user from the administration panel.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $user->name = $request->validated('name');
        $user->email = $request->validated('email');
        $user->is_admin = $request->boolean('is_admin');

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('status', 'Usuário atualizado com sucesso.');
    }

    /**
     * Toggle the active status of a registered user.
     */
    public function toggle(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'Você não pode desativar a própria conta.']);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('status', $user->is_active
            ? 'Usuário ativado com sucesso.'
            : 'Usuário desativado com sucesso.');
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
