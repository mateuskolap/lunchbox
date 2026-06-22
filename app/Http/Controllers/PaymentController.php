<?php

namespace App\Http\Controllers;

use App\Actions\Payments\CreatePaymentAction;
use App\Data\Payments\CreatePaymentData;
use App\Enums\PaymentMethodEnum;
use App\Models\Customer;
use App\Models\Payment;
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
    )
    {
    }

    public function customerIndex(Customer $customer): Response
    {
        return Inertia::render('Payments/CustomerIndex', [
            'payments' => $customer->payments()->paginate(25),
            'customer' => $customer,
        ]);
    }

    public function show(Payment $payment): Response
    {
        return Inertia::render('Payments/Show', [
            'orderPayments' => $payment->orders()->paginate(25),
            'payment' => $payment,
            'customer' => $payment->customer,
        ]);
    }

    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'method' => ['required', new Enum(PaymentMethodEnum::class)],
            'value' => ['required', 'numeric'],
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
}
