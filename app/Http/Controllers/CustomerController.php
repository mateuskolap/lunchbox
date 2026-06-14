<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Customers/Index', [
            'customers' => Customer::paginate(20),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'phone' => ['required', 'string', 'min:10', 'max:11'],
        ]);

        Customer::create($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Cliente criado com sucesso!',
        ]);

        return redirect()->route('customers.index');
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'phone' => ['required', 'string', 'min:10', 'max:11'],
        ]);

        $customer->update($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Cliente atualizado com sucesso!',
        ]);

        return redirect()->route('customers.index');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Cliente excluído com sucesso!',
        ]);

        return redirect()->route('customers.index');
    }
}
