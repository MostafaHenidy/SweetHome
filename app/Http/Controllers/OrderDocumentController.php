<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderDocumentController extends Controller
{
    public function signed(Request $request, Order $order, string $document): StreamedResponse
    {
        if (! $request->hasValidSignature()) {
            abort(403);
        }

        return $this->stream($order, $document);
    }

    public function authenticated(Request $request, Order $order, string $document): StreamedResponse
    {
        $this->assertCanAccess($request, $order);

        return $this->stream($order, $document);
    }

    protected function assertCanAccess(Request $request, Order $order): void
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        if ($order->user_id !== null && (int) $order->user_id === (int) $user->id) {
            return;
        }

        if (strcasecmp((string) $order->customer_email, (string) $user->email) === 0) {
            return;
        }

        abort(403);
    }

    protected function stream(Order $order, string $document): StreamedResponse
    {
        $path = match ($document) {
            'confirmation' => $order->order_confirmation_pdf_path,
            'invoice' => $order->invoice_pdf_path,
            default => abort(404),
        };

        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $downloadName = match ($document) {
            'confirmation' => 'modulo_ordine_'.$order->order_number.'.pdf',
            'invoice' => 'fattura_'.$order->order_number.'.pdf',
            default => 'documento.pdf',
        };

        return Storage::disk('public')->download($path, $downloadName);
    }
}
