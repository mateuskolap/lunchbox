<?php

namespace App\Http\Controllers;

use App\Actions\Payments\CancelPaymentAction;
use App\Actions\Payments\CreatePaymentAction;
use App\Data\Payments\CreatePaymentData;
use App\Enums\PaymentMethodEnum;
use App\Models\Customer;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
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

        $start = ($validated['start_date'] ?? null) ? Carbon::parse($validated['start_date'])->startOfDay() : null;
        $end = ($validated['end_date'] ?? null) ? Carbon::parse($validated['end_date'])->startOfDay() : null;

        return Inertia::render('Payments/CustomerIndex', [
            'payments' => $this->filterDates($customer->payments(), 'paid_at', $start, $end)
                ->latest()
                ->paginate(15, ['*'], 'payments_page'),
            'transactions' => $this->filterDates($customer->transactions(), 'created_at', $start, $end)
                ->latest()
                ->paginate(15, ['*'], 'transactions_page'),
            'unpaid_orders' => $this->filterDates($customer->orders(), 'date', $start, $end)
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

    /**
     * @template TModel of Model
     *
     * @param Builder<TModel>|Relation<TModel> $query
     * @return Builder<TModel>|Relation<TModel>
     */
    private function filterDates(
        Builder|Relation $query,
        string           $column,
        ?Carbon          $start = null,
        ?Carbon          $end = null
    ): Builder|Relation
    {
        return $query
            ->when($start, fn($q) => $q->where($column, '>=', $start))
            ->when($end, fn($q) => $q->where($column, '<=', $end));
    }
}
