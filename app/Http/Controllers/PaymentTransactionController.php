<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The seller-facing counterpart to Admin\TransactionController — same
 * underlying Payment rows (auto-scoped to the current business via
 * BelongsToTenant), just without the cross-tenant/admin-only columns.
 * Purely presentational: no new calculation, everything here was already
 * computed and stored by CommissionService/PaymentService.
 */
class PaymentTransactionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Payment::class);

        $payments = Payment::with('order.customer')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $successful = Payment::where('status', 'success');
        $totals = [
            'sales' => (clone $successful)->sum('amount') / 100,
            'fees' => (clone $successful)->sum('commission_amount') / 100,
            'net' => (clone $successful)->sum('seller_amount') / 100,
        ];

        return view('payments.index', compact('payments', 'totals'));
    }
}
