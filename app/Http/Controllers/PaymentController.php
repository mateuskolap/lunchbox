<?php

namespace App\Http\Controllers;

use App\Actions\Payments\CancelPaymentAction;
use App\Actions\Payments\CreatePaymentAction;
use App\Data\Payments\CreatePaymentData;
use App\Enums\PaymentMethodEnum;
use App\Models\Customer;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class PaymentController extends Controller
{
    public function __construct(
        private readonly CreatePaymentAction $createPayment,
        private readonly CancelPaymentAction $cancelPayment,
    )
    {
    }

    public function customerIndex(Request $request, Customer $customer): Response
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ]);

        $startDate = ($validated['start_date'] ?? null) ? Carbon::parse($validated['start_date'])->startOfDay() : null;
        $endDate = ($validated['end_date'] ?? null) ? Carbon::parse($validated['end_date'])->startOfDay() : null;

        return Inertia::render('Payments/CustomerIndex', [
            'payments' => $customer->payments()
                ->when($startDate, function ($query) use ($startDate) {
                    $query->where('paid_at', '>=', Carbon::parse($startDate)->startOfDay());
                })
                ->when($endDate, function ($query) use ($endDate) {
                    $query->where('paid_at', '<=', Carbon::parse($endDate)->endOfDay());
                })
                ->latest()
                ->paginate(15, ['*'], 'payments_page'),
            'transactions' => $customer->transactions()
                ->when($startDate, function ($query) use ($startDate) {
                    $query->where('created_at', '>=', Carbon::parse($startDate)->startOfDay());
                })
                ->when($endDate, function ($query) use ($endDate) {
                    $query->where('created_at', '<=', Carbon::parse($endDate)->endOfDay());
                })
                ->latest()
                ->paginate(15, ['*'], 'transactions_page'),
            'unpaid_orders' => $customer->orders()
                ->when($startDate, function ($query) use ($startDate) {
                    $query->where('date', '>=', Carbon::parse($startDate)->startOfDay());
                })
                ->when($endDate, function ($query) use ($endDate) {
                    $query->where('date', '<=', Carbon::parse($endDate)->endOfDay());
                })
                ->unpaid()
                ->paginate(15, ['*'], 'unpaid_orders_page'),
            'customer' => $customer,
            'filters' => $request->only(['start_date', 'end_date']),
        ]);
    }

    public function store(Request $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validate([
            'method' => ['required', new Enum(PaymentMethodEnum::class)],
            'value' => ['required', 'numeric', 'min:0.01'],
            'paid_at' => ['nullable', 'date'],
        ]);

        try {
            $this->createPayment->execute($customer, CreatePaymentData::from($validated));

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Pagamento registrado com sucesso!',
            ]);

            return redirect()->route('customers.payments.customer-index', $customer);
        } catch (Throwable $e) {
            Log::error('Erro ao registrar pagamento: ' . $e->getMessage(), [
                'customer_id' => $customer->id,
                'exception' => $e,
                'request_data' => $validated,
            ]);

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Ocorreu um erro ao registrar o pagamento.',
            ]);

            return back();
        }
    }

    public function cancel(Customer $customer, Payment $payment): RedirectResponse
    {
        try {
            $this->cancelPayment->execute($payment);

            Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Pagamento cancelado com sucesso!',
            ]);

            return redirect()->route('customers.payments.customer-index', $customer);
        } catch (Throwable $e) {
            Log::error('Erro ao cancelar pagamento: ' . $e->getMessage(), [
                'customer_id' => $customer->id,
                'exception' => $e,
            ]);

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Ocorreu um erro ao cancelar o pagamento.',
            ]);

            return back();
        }
    }
}
