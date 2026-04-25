<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function initiate(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        $request->validate([
            'method' => 'required|in:stripe,paypal,bank_transfer',
        ]);

        if ($request->method === 'bank_transfer') {
            $this->paymentService->markOrderPaid($order);
            return redirect()->route('checkout.success', $order->order_number);
        }

        if ($request->method === 'stripe') {
            $checkoutUrl = $this->paymentService->processStripeCheckout($order);
            return redirect($checkoutUrl);
        }

        if ($request->method === 'paypal') {
            $result = $this->paymentService->processPayPalCheckout($order);
            Session::put('paypal_order_id', $result['order_id']);
            return redirect($result['redirect_url']);
        }

        return redirect()->route('checkout');
    }

    public function success(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();

        if ($request->has('session')) {
            $this->paymentService->markOrderPaid($order);
        }

        return redirect()->route('checkout.success', $order->order_number);
    }

    public function cancel(string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $this->paymentService->markOrderFailed($order);

        return redirect()->route('checkout')
            ->with('error', 'Pagamento annullato. Riprova.');
    }

    public function paypalReturn(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $paypalOrderId = Session::get('paypal_order_id');

        if ($paypalOrderId && $this->paymentService->capturePayPalOrder($paypalOrderId)) {
            $this->paymentService->markOrderPaid($order);
            Session::forget('paypal_order_id');
            return redirect()->route('checkout.success', $order->order_number);
        }

        $this->paymentService->markOrderFailed($order);
        return redirect()->route('checkout')
            ->with('error', 'Pagamento PayPal non completato.');
    }
}
