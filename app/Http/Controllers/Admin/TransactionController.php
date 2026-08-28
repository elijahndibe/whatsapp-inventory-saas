<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Admin > Transactions: reads the existing payments table directly rather
 * than a separate ledger table — every payment already carries its
 * commission snapshot (see Payment/CommissionService).
 */
class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $payments = $this->filtered($request)
            ->with(['business', 'order.customer'])
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $businesses = \App\Models\Business::orderBy('name')->get(['id', 'name']);

        return view('admin.transactions.index', compact('payments', 'businesses'));
    }

    public function export(Request $request): Response
    {
        $payments = $this->filtered($request)->with(['business', 'order.customer'])->latest('id')->get();

        $csv = "Transaction ID,Seller,Order,Source,Customer,Gross Amount,Commission Rate,Commission,Payment Fee,Seller Amount,Gateway,Status,Date\n";

        foreach ($payments as $payment) {
            $csv .= implode(',', [
                $payment->reference,
                '"'.str_replace('"', '""', $payment->business?->name ?? '').'"',
                $payment->order?->order_number,
                $payment->order?->source,
                '"'.str_replace('"', '""', $payment->order?->customer?->name ?? '').'"',
                $payment->amount,
                $payment->commission_rate,
                $payment->commission_amount,
                $payment->payment_fee,
                $payment->seller_amount,
                $payment->gateway,
                $payment->status,
                $payment->created_at?->toDateTimeString(),
            ])."\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="transactions.csv"',
        ]);
    }

    private function filtered(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        return Payment::withoutGlobalScopes()
            ->when($request->filled('business_id'), fn ($q) => $q->where('business_id', $request->integer('business_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('gateway'), fn ($q) => $q->where('gateway', $request->string('gateway')))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('date_to')))
            ->when($request->filled('min_amount'), fn ($q) => $q->where('amount', '>=', (int) round($request->float('min_amount') * 100)))
            ->when($request->filled('max_amount'), fn ($q) => $q->where('amount', '<=', (int) round($request->float('max_amount') * 100)));
    }
}
