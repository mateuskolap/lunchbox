<?php

namespace App\Http\Controllers;

use App\Actions\Orders\CancelOrderAction;
use App\Actions\Orders\CreateOrderWithItemsAction;
use App\Actions\Orders\ReopenOrderAction;
use App\Actions\Orders\UpdateOrderWithItemsAction;
use App\Data\Orders\CreateOrderWithItemsData;
use App\Data\Orders\UpdateOrderWithItemsData;
use App\Data\WhatsApp\SendTextMessageData;
use App\Enums\OrderStatusEnum;
use App\Jobs\SendWhatsAppTextMessageJob;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;
use LogicException;
use Throwable;

class OrderController extends Controller
{
    public function __construct(
        private readonly CreateOrderWithItemsAction $createOrderWithItems,
        private readonly UpdateOrderWithItemsAction $updateOrderWithItems,
        private readonly CancelOrderAction          $cancelOrder,
        private readonly ReopenOrderAction          $reopenOrder,
    )
    {
    }

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string'],
            'status' => ['nullable', new Enum(OrderStatusEnum::class)],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'customer_id' => ['nullable', 'exists:customers,id'],
        ]);

        session(['orders_list_url' => route('orders.index')]);

        return Inertia::render('Orders/Index', [
            'orders' => Order::with('customer')
                ->when($validated['customer_name'] ?? null, function ($query) use ($validated) {
                    $query->whereHas('customer', fn($q) => $q->whereLike('name', "%{$validated['customer_name']}%"));
                })
                ->when($validated['status'] ?? null, function ($query) use ($validated) {
                    $query->where('status', $validated['status']);
                })
                ->when($validated['start_date'] ?? null, function ($query, $startDate) {
                    $query->where('date', '>=', Carbon::parse($startDate)->startOfDay());
                })
                ->when($validated['end_date'] ?? null, function ($query, $endDate) {
                    $query->where('date', '<=', Carbon::parse($endDate)->endOfDay());
                })
                ->when($validated['customer_id'] ?? null, function ($query, $customerId) {
                    $query->where('customer_id', $customerId);
                })
                ->latest()
                ->paginate(25),
            'order_statuses' => OrderStatusEnum::cases(),
            'filters' => $request->only(['customer_name', 'status', 'start_date', 'end_date', 'customer_id']),
        ]);
    }

    public function todayIndex(): Response
    {
        session(['orders_list_url' => route('orders.today')]);

        return Inertia::render('Orders/Today', [
            'orders' => Order::with('customer')
                ->whereBetween('date', [now()->startOfDay(), now()->endOfDay()])
                ->with(['items', 'items.product'])
                ->latest()
                ->get(),
        ]);
    }

    public function show(Order $order): Response
    {
        return Inertia::render('Orders/Show', [
            'order' => $order->load(['customer', 'items.product', 'transactions']),
            'list_url' => session('orders_list_url', route('orders.index')),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Orders/Create', [
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'list_url' => session('orders_list_url', route('orders.index')),
        ]);
    }

    public function edit(Order $order): Response
    {
        return Inertia::render('Orders/Edit', [
            'order' => $order->load(['customer', 'items.product']),
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
            'list_url' => session('orders_list_url', route('orders.index')),
        ]);
    }

    /**
     * @throws Throwable
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'observation' => ['nullable', 'string'],
            'date' => ['nullable', 'date'],
            'order_items' => ['required', 'array'],
            'order_items.*.product_id' => ['required', 'exists:products,id'],
            'order_items.*.quantity' => ['required', 'integer', 'min:1'],
            'order_items.*.unit_price' => ['nullable', 'numeric', 'min:0.01']
        ]);

        try {
            $this->createOrderWithItems->execute(CreateOrderWithItemsData::from($validated));

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Pedido criado com sucesso!',
            ]);

            return redirect(session('orders_list_url', route('orders.index')));
        } catch (Throwable $e) {
            Log::error('Erro ao criar pedido: ' . $e->getMessage(), [
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
            'date' => ['required', 'date'],
            'observation' => ['nullable', 'string'],
            'order_items' => ['required', 'array'],
            'order_items.*.product_id' => ['required', 'exists:products,id'],
            'order_items.*.quantity' => ['required', 'integer', 'min:1'],
            'order_items.*.unit_price' => ['nullable', 'numeric', 'min:0.01']
        ]);

        try {
            $this->updateOrderWithItems->execute($order, UpdateOrderWithItemsData::from($validated));

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Pedido atualizado com sucesso!',
            ]);

            return redirect()->route('orders.show', $order);
        } catch (Throwable $e) {
            Log::error('Erro ao atualizar pedido: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'exception' => $e,
                'request_data' => $validated,
            ]);

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Ocorreu um erro ao atualizar o pedido.',
            ]);

            return back();
        }
    }

    public function conclude(Order $order): RedirectResponse
    {
        try {
            $order->conclude();
        } catch (LogicException $e) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);

            return redirect(session('orders_list_url', route('orders.index')));
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pedido concluído com sucesso!',
        ]);

        return redirect(session('orders_list_url', route('orders.index')));
    }

    public function cancel(Order $order): RedirectResponse
    {
        if ($order->status === OrderStatusEnum::CANCELED) {
            return redirect(session('orders_list_url', route('orders.index')));
        }

        try {
            $this->cancelOrder->execute($order);
        } catch (Throwable $e) {
            Log::error('Erro ao cancelar pedido: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'exception' => $e,
            ]);

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Ocorreu um erro ao cancelar o pedido.',
            ]);

            return back();
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pedido cancelado com sucesso!',
        ]);

        return redirect(session('orders_list_url', route('orders.index')));
    }

    public function reopen(Order $order): RedirectResponse
    {
        try {
            $this->reopenOrder->execute($order);
        } catch (LogicException $e) {
            Inertia::flash('toast', [
                'type' => 'error',
                'message' => $e->getMessage(),
            ]);

            return redirect(session('orders_list_url', route('orders.index')));
        } catch (Throwable $e) {
            Log::error('Erro ao reabrir pedido: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'exception' => $e,
            ]);

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Ocorreu um erro ao reabrir o pedido.',
            ]);

            return redirect(session('orders_list_url', route('orders.index')));
        }

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pedido reaberto com sucesso!',
        ]);

        return redirect()->route('orders.show', $order);
    }
}
