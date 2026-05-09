<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
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

    public function store(): RedirectResponse
    {
        return redirect()->route('users.index');
    }

    public function update(): RedirectResponse
    {
        return redirect()->route('users.index');
    }

    public function destroy(): RedirectResponse
    {
        return redirect()->route('users.index');
    }
}
