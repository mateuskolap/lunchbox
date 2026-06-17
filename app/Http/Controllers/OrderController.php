<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string'],
            'status' => ['nullable', new Enum(OrderStatusEnum::class)],
            'date' => ['nullable', 'date'],
        ]);

        return Inertia::render('Orders/Index', [
            'orders' => Order::with('customer')
                ->latest()
                ->when($validated['customer_name'] ?? null, function ($query) use ($validated) {
                    $query->whereHas('customer', fn ($q) => $q->where('name', 'like', "%{$validated['customer_name']}%"));
                })
                ->when($validated['status'] ?? null, function ($query) use ($validated) {
                    $query->where('status', $validated['status']);
                })
                ->when($validated['date'] ?? null, function ($query) use ($validated) {
                    $query->whereDate('date', $validated['date']);
                })
                ->paginate(25),
            'order_statuses' => OrderStatusEnum::cases(),
            'filters' => $request->only(['customer_name', 'status', 'date']),
        ]);
    }

    public function show(Order $order): Response
    {
        return Inertia::render('Orders/Show', [
            'order' => $order->load(['customer', 'items.product']),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Orders/Create', [
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    public function edit(Order $order): Response
    {
        return Inertia::render('Orders/Edit', [
            'order' => $order->load(['customer', 'items.product']),
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'total_amount' => ['required', 'numeric'],
            'observation' => ['nullable', 'string'],
            'date' => ['nullable', 'date'],
            'order_items' => ['required', 'array'],
            'order_items.*.product_id' => ['required', 'exists:products,id'],
            'order_items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $order = Order::create([
                    'customer_id' => $validated['customer_id'],
                    'total_amount' => $validated['total_amount'],
                    'status' => OrderStatusEnum::PENDING,
                    'observation' => $validated['observation'] ?? null,
                    'date' => $validated['date'] ?? now(),
                ]);

                $orderItems = $this->prepareOrderItems($validated['order_items']);
                $order->items()->createMany($orderItems->all());
                $order->recalculateTotals();
            });

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Pedido criado com sucesso!',
            ]);

            return redirect()->route('orders.index');
        } catch (Throwable $e) {
            Log::error('Erro ao criar pedido: '.$e->getMessage(), [
                'exception' => $e,
                'request_data' => $validated,
            ]);

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Ocorreu um erro ao processar a criação do pedido.',
            ]);

            return back();
        }
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'total_amount' => ['required', 'numeric'],
            'date' => ['required', 'date'],
            'observation' => ['nullable', 'string'],
        ]);

        $order->update($validated);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pedido atualizado com sucesso!',
        ]);

        return redirect()->route('orders.show', $order);
    }

    public function addItems(Request $request, Order $order): RedirectResponse
    {
        if ($order->isClosed()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Não é possível editar os itens de um pedido encerrado!',
            ]);

            return redirect()->route('orders.show', $order);
        }

        $validated = $request->validate([
            'order_items' => ['required', 'array'],
            'order_items.*.product_id' => ['required', 'exists:products,id'],
            'order_items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        try {
            DB::transaction(function () use ($validated, $order) {
                $orderItems = $this->prepareOrderItems($validated['order_items']);
                $order->items()->createMany($orderItems->all());
                $order->recalculateTotals();
            });

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Pedido atualizado com sucesso!',
            ]);

            return redirect()->route('orders.show', $order);
        } catch (Throwable $e) {
            Log::error('Erro ao adicionar itens ao pedido: '.$e->getMessage(), [
                'order_id' => $order->id,
                'exception' => $e,
            ]);

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Ocorreu um erro ao adicionar itens ao pedido.',
            ]);

            return back();
        }
    }

    public function removeItem(Order $order, OrderItem $item): RedirectResponse
    {
        if ($order->isClosed()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Não é possível editar os itens de um pedido encerrado!',
            ]);

            return redirect()->route('orders.show', $order);
        }

        try {
            DB::transaction(function () use ($order, $item) {
                $item->delete();
                $order->recalculateTotals();
            });

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Pedido atualizado com sucesso!',
            ]);

            return redirect()->route('orders.show', $order);
        } catch (Throwable $e) {
            Log::error('Erro ao remover item do pedido: '.$e->getMessage(), [
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'exception' => $e,
            ]);

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Ocorreu um erro ao remover o item do pedido.',
            ]);

            return back();
        }
    }

    public function conclude(Order $order): RedirectResponse
    {
        $order->update(['status' => OrderStatusEnum::CONCLUDED]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pedido concluído com sucesso!',
        ]);

        return redirect()->route('orders.show', $order);
    }

    public function cancel(Order $order): RedirectResponse
    {
        $order->update(['status' => OrderStatusEnum::CANCELED]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pedido cancelado com sucesso!',
        ]);

        return redirect()->route('orders.show', $order);
    }

    public function reopen(Order $order): RedirectResponse
    {
        if (! $order->isClosed()) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Apenas pedidos concluídos ou cancelados podem ser reabertos!',
            ]);

            return redirect()->route('orders.index');
        }

        $order->update(['status' => OrderStatusEnum::PENDING]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pedido reaberto com sucesso!',
        ]);

        return redirect()->route('orders.show', $order);
    }

    private function prepareOrderItems(array $items): Collection
    {
        $products = Product::whereIn('id', collect($items)->pluck('product_id'))
            ->get()
            ->keyBy('id');

        return collect($items)->map(function ($item) use ($products) {
            $product = $products->get($item['product_id']);
            $price = $product ? $product->price : 0.00;

            return [
                'product_id' => $item['product_id'],
                'unit_price' => $price,
                'quantity' => $item['quantity'],
                'total_amount' => $price * $item['quantity'],
            ];
        });
    }
}
