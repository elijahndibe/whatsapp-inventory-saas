<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    public function invoice(Order $order): Response
    {
        $this->authorize('view', $order);

        return $this->render($order, 'invoice');
    }

    public function receipt(Order $order): Response
    {
        $this->authorize('view', $order);

        abort_unless($order->payment_status === 'paid', 404);

        return $this->render($order, 'receipt');
    }

    private function render(Order $order, string $documentType): Response
    {
        $order->load(['items', 'customer', 'business']);

        $pdf = Pdf::loadView('pdfs.order-document', [
            'order' => $order,
            'business' => $order->business,
            'documentType' => $documentType,
        ])->setPaper('a4');

        return $pdf->stream("{$documentType}-{$order->order_number}.pdf");
    }
}
