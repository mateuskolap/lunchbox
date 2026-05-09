<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::create(['name' => 'users.index']);
    Permission::create(['name' => 'users.store']);
    Permission::create(['name' => 'users.update']);
    Permission::create(['name' => 'users.destroy']);
});

it('renders the users index page', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(['users.index', 'users.store']);

    $this->actingAs($user)
        ->get(route('users.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Users/Index')
            ->has('auth.permissions', 2)
        );
});

it('can create a user', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(['users.store', 'users.index']);

    $userData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    $this->actingAs($admin)
        ->post(route('users.store'), $userData)
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('flash.toast.type', 'success');

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

it('can update a user', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(['users.update', 'users.index']);
    $userToEdit = User::factory()->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
    ]);

    $updateData = [
        'name' => 'Jane Smith',
        'email' => 'janesmith@example.com',
    ];

    $this->actingAs($admin)
        ->put(route('users.update', $userToEdit), $updateData)
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('flash.toast.type', 'success');

    $this->assertDatabaseHas('users', [
        'id' => $userToEdit->id,
        'name' => 'Jane Smith',
        'email' => 'janesmith@example.com',
    ]);
});

it('can delete a user', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(['users.destroy', 'users.index']);
    $userToDelete = User::factory()->create();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $userToDelete))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('flash.toast.type', 'success');

    $this->assertSoftDeleted($userToDelete);
});

it('prevents self deletion', function () {
    $admin = User::factory()->create();
    $admin->givePermissionTo(['users.destroy', 'users.index']);

    $this->actingAs($admin)
        ->delete(route('users.destroy', $admin))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('flash.toast.type', 'error')
        ->assertSessionHas('flash.toast.message', 'Você não pode excluir sua própria conta!');

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
        'deleted_at' => null,
    ]);
});
