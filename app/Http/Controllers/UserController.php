<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Users/Index', [
            'users' => User::paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
        ]);

        User::create($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Usuário criado com sucesso!',
        ]);

        return redirect()->route('users.index');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
        ]);

        $user->update(array_filter($validated));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Usuário atualizado com sucesso!',
        ]);

        return redirect()->route('users.index');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Você não pode excluir sua própria conta!',
            ]);

            return redirect()->route('users.index');
        }

        $user->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Usuário excluído com sucesso!',
        ]);

        return redirect()->route('users.index');
    }
}
