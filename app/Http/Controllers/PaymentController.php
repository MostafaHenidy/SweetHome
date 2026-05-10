<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\PaymentInstallment;
use App\Services\PaymentService;
use App\Services\PaymentInstallmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected PaymentInstallmentService $installmentService
    ) {}

    public function initiate(Request $request, string $orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)->firstOrFail();
        $method = $request->input('method', $order->payment_method);
        $installmentNumber = $request->input('installment', 1);

        validator(['method' => $method], [
            'method' => 'required|in:stripe,paypal,bank_transfer,fawaterak',
        ])->validate();

        // For orders with installments, get the specific installment to pay
        $installment = null;
        if ($order->paymentInstallments()->exists()) {
            $installment = $order->paymentInstallments()
                ->where('installment_number', $installmentNumber)
                ->where('status', 'pending')
                ->first();

            if (!$installment) {
                // If no pending installment found, check if all are paid
                if ($this->installmentService->areAllInstallmentsPaid($order)) {
                    return redirect()->route('checkout.success', $order->order_number);
                }
                // Otherwise get the next pending installment
                $installment = $this->installmentService->getNextInstallmentToPay($order);
            }

            // If no installment to pay, redirect to success
            if (!$installment) {
                return redirect()->route('checkout.success', $order->order_number);
            }

            $amount = $installment->amount;
        } else {
            // Legacy order without installments
            $amount = (float) $order->total;
        }

        // Handle zero amount orders
        if ($amount <= 0) {
            if ($installment) {
                $this->installmentService->markInstallmentPaid($installment, 'zero_amount');
            } else {
                $this->paymentService->markOrderPaid($order);
            }

            return redirect()->route('checkout.success', $order->order_number);
        }

        if ($method === 'bank_transfer') {
            if ($installment) {
                $this->installmentService->markInstallmentPaid($installment, 'bank_transfer_pending');
            } else {
                $this->paymentService->markOrderAwaitingPayment($order);
            }

            return redirect()->route('checkout.success', $order->order_number);
        }

        if ($method === 'fawaterak') {
            // For Fawaterak, we'll handle the payment integration
            // This would need to be implemented with actual Fawaterak API
            if ($installment) {
                // Store installment info in session for Fawaterak callback
                Session::put('fawaterak_installment_id', $installment->id);
                Session::put('fawaterak_order_number', $orderNumber);
            }

            // For now, mark as successful (in production, this would call Fawaterak API)
            // TODO: Implement actual Fawaterak integration
            if ($installment) {
                $this->installmentService->markInstallmentPaid($installment, 'fawaterak_mock_'.time());
            } else {
                $this->paymentService->markOrderPaid($order);
            }

            return redirect()->route('checkout.success', $order->order_number);
        }

        if ($method === 'stripe') {
            $checkoutUrl = $this->paymentService->processStripeCheckout($order);

            return redirect($checkoutUrl);
        }

        if ($method === 'paypal') {
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
            // Check if this is an installment payment
            $installmentId = Session::get('fawaterak_installment_id');
            if ($installmentId) {
                $installment = PaymentInstallment::find($installmentId);
                if ($installment) {
                    $this->installmentService->markInstallmentPaid($installment, $request->input('session'));
                }
                Session::forget('fawaterak_installment_id');
                Session::forget('fawaterak_order_number');
            } else {
                // Legacy full order payment
                $this->paymentService->markOrderPaid($order);
            }
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
