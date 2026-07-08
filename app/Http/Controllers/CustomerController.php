<?php

namespace App\Http\Controllers;

use App\Data\WhatsApp\SendTextMessageData;
use App\Enums\OrderStatusEnum;
use App\Models\Customer;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'name' => ['nullable', 'string'],
            'debtors_only' => ['nullable', 'bool'],
        ]);

        return Inertia::render('Customers/Index', [
            'customers' => Customer::query()
                ->when($validated['name'] ?? null, fn($q) => $q->whereLike('name', "%{$validated['name']}%"))
                ->when($validated['debtors_only'] ?? null, fn($q) => $q->where('balance', '<', 0))
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString(),
            'filters' => $request->only(['name', 'debtors_only']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'min:10', 'max:11'],
        ]);

        Customer::create(array_filter($validated));

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
            'phone' => ['nullable', 'string', 'min:10', 'max:11'],
        ]);

        $customer->update(array_filter($validated));

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Cliente atualizado com sucesso!',
        ]);

        return redirect()->route('customers.index');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->orders()->whereNot('status', OrderStatusEnum::CANCELED)->exists()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Você não pode excluir um cliente que possua pedidos!',
            ]);
        }

        $customer->delete();

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Cliente excluído com sucesso!',
        ]);

        return redirect()->route('customers.index');
    }
}
