<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Roles/Index', [
            'roles' => Role::with('permissions')->paginate(25),
            'permissions' => Permission::all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:roles,name'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $validated['name'],
        ]);

        $role->permissions()->sync($validated['permission_ids'] ?? []);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Papél criado com sucesso!',
        ]);

        return redirect()->route('roles.index');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'unique:roles,name,' . $role->id],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        $role->permissions()->sync($validated['permission_ids'] ?? []);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Papél atualizado com sucesso!',
        ]);

        return redirect()->route('roles.index');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Papél excluído com sucesso!',
        ]);

        return redirect()->route('roles.index');
    }
}
