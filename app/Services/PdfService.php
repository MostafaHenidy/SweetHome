<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class PdfService
{
    /**
     * Generate PDF from view using native HTML to PDF conversion
     */
    public function generateFromView($view, $data = [], $filename = null)
    {
        // Render the HTML view
        $html = View::make($view, $data)->render();

        // Clean up HTML for PDF generation
        $html = $this->cleanHtmlForPdf($html);

        // Generate a simple PDF-like text file with proper formatting
        // In production, you would use a proper PDF library like dompdf or snappy
        $pdfContent = $this->generateSimplePdf($html, $data);

        return $pdfContent;
    }

    /**
     * Generate a simple formatted PDF-like document
     */
    protected function generateSimplePdf($html, $data)
    {
        $order = $data['order'] ?? null;
        $items = $data['items'] ?? [];
        $customer = $data['customer'] ?? null;

        $content = [];
        $content[] = '═══════════════════════════════════════════════════════════════════════════';
        $content[] = '                         PREVENTIVO / QUOTATION';
        $content[] = '═══════════════════════════════════════════════════════════════════════════';
        $content[] = '';

        if ($order) {
            $content[] = 'Numero Preventivo: '.$order->order_number;
            $content[] = 'Data: '.($order->created_at ? $order->created_at->format('d/m/Y H:i') : '');
            if ($order->quote_expires_at) {
                $content[] = 'Valido fino a: '.$order->quote_expires_at->format('d/m/Y H:i');
            }
            $content[] = '';
        }

        $content[] = 'INFORMAZIONI CLIENTE';
        $content[] = '───────────────────────────────────────────────────────────────────────────';
        if ($customer) {
            $content[] = 'Nome: '.($customer->name ?? $order->customer_name ?? '');
            $content[] = 'Email: '.($customer->email ?? $order->customer_email ?? '');
            $content[] = 'Telefono: '.($customer->phone ?? $order->customer_phone ?? '');
        } else {
            $content[] = 'Nome: '.($order->customer_name ?? '');
            $content[] = 'Email: '.($order->customer_email ?? '');
            $content[] = 'Telefono: '.($order->customer_phone ?? '');
        }
        $content[] = '';

        // Shipping address
        if ($order && $order->shipping_address) {
            $content[] = 'INDIRIZZO DI SPEDIZIONE';
            $content[] = '───────────────────────────────────────────────────────────────────────────';
            $addr = $order->shipping_address;
            if (is_array($addr)) {
                $content[] = ($addr['address'] ?? '').', '.($addr['city'] ?? '');
                $content[] = ($addr['state'] ?? '').' '.($addr['zip_code'] ?? '');
                $content[] = ($addr['country'] ?? '');
            } else {
                $content[] = $addr;
            }
            $content[] = '';
        }

        // Order items
        $content[] = 'DETTAGLI PRODOTTI';
        $content[] = '───────────────────────────────────────────────────────────────────────────';
        $content[] = str_pad('PRODOTTO', 40).str_pad('QTA', 8).str_pad('PREZZO', 12).str_pad('TOTALE', 12);
        $content[] = '───────────────────────────────────────────────────────────────────────────';

        $subtotal = 0;
        foreach ($items as $item) {
            $productName = substr($item->product_name ?? $item['product_name'] ?? 'Prodotto', 0, 37);
            $quantity = $item->quantity ?? $item['quantity'] ?? 1;
            $unitPrice = $item->unit_price ?? $item['unit_price'] ?? 0;
            $totalPrice = $item->total_price ?? $item['total_price'] ?? ($quantity * $unitPrice);
            $subtotal += $totalPrice;

            $content[] = str_pad($productName, 40).
                        str_pad((string) $quantity, 8).
                        str_pad('€'.number_format($unitPrice, 2), 12).
                        str_pad('€'.number_format($totalPrice, 2), 12);
        }

        $content[] = '───────────────────────────────────────────────────────────────────────────';
        $content[] = '';

        // Totals
        $content[] = 'RIEPILOGO TOTALE';
        $content[] = '───────────────────────────────────────────────────────────────────────────';
        $content[] = str_pad('Subtotale:', 50).str_pad('€'.number_format($subtotal, 2), 12);

        if ($order) {
            if ($order->shipping_cost > 0) {
                $content[] = str_pad('Spese di spedizione:', 50).str_pad('€'.number_format($order->shipping_cost, 2), 12);
            }
            if ($order->tax > 0) {
                $content[] = str_pad('IVA:', 50).str_pad('€'.number_format($order->tax, 2), 12);
            }
            if ($order->discount_amount > 0) {
                $content[] = str_pad('Sconto:', 50).str_pad('-€'.number_format($order->discount_amount, 2), 12);
            }
        }

        $total = $order ? $order->total : $subtotal;
        $content[] = str_pad('TOTALE:', 50).str_pad('€'.number_format($total, 2), 12);
        $content[] = '';

        // Notes
        if ($order && $order->notes) {
            $content[] = 'NOTE';
            $content[] = '───────────────────────────────────────────────────────────────────────────';
            $content[] = $order->notes;
            $content[] = '';
        }

        // Footer
        $content[] = '═══════════════════════════════════════════════════════════════════════════';
        $content[] = 'SweetHome Milano';
        $content[] = 'Via Roma 123, 20100 Milano';
        $content[] = 'Tel: +39 02 1234567 | Email: info@sweethome.it';
        $content[] = 'P.IVA: IT12345678901';
        $content[] = '═══════════════════════════════════════════════════════════════════════════';

        return implode("\n", $content);
    }

    /**
     * Clean HTML for PDF generation
     */
    protected function cleanHtmlForPdf($html)
    {
        // Remove scripts and styles
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);

        // Convert common HTML entities
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($html);
    }

    /**
     * Save PDF to storage
     */
    public function saveToStorage($view, $data, $path)
    {
        $content = $this->generateFromView($view, $data);
        Storage::disk('local')->put($path, $content);

        return $path;
    }

    /**
     * Generate order confirmation PDF
     */
    public function generateOrderConfirmation($order)
    {
        return $this->generateFromView('pdf.order-confirmation', [
            'order' => $order,
            'items' => $order->items,
        ]);
    }

    /**
     * Generate quote PDF
     */
    public function generateQuote($order)
    {
        return $this->generateFromView('pdf.quote', [
            'order' => $order,
            'customer' => $order->customer,
            'items' => $order->items,
        ]);
    }

    /**
     * Get PDF content as base64
     */
    public function getBase64($view, $data = [])
    {
        $content = $this->generateFromView($view, $data);

        return base64_encode($content);
    }
}
