<?php

namespace App\Http\Controllers;

use App\Actions\Payments\CancelPaymentAction;
use App\Actions\Payments\CreatePaymentAction;
use App\Data\Payments\CreatePaymentData;
use App\Enums\PaymentMethodEnum;
use App\Models\Customer;
use App\Models\Payment;
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

    public function customerIndex(Customer $customer): Response
    {
        return Inertia::render('Payments/CustomerIndex', [
            'payments' => $customer->payments()->latest()->paginate(15, ['*'], 'payments_page')->toArray(),
            'transactions' => $customer->transactions()->latest()->paginate(15, ['*'], 'transactions_page'),
            'customer' => $customer,
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
