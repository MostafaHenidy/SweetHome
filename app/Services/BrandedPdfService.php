<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use mikehaertl\pdftk\Pdf as PdftkPdf;
use setasign\Fpdi\Tcpdf\Fpdi;

class BrandedPdfService
{
    public function generateAndStoreQuote(Order $order): string
    {
        $bytes = $this->generatePdf($order, 'quote');
        $path = 'quotes/quote_'.$order->order_number.'_'.time().'.pdf';
        Storage::disk('public')->put($path, $bytes);

        return $path;
    }

    public function generateAndStoreOrderConfirmation(Order $order): string
    {
        $bytes = $this->generatePdf($order, 'order_confirmation');
        $path = 'orders/confirmation_'.$order->order_number.'_'.time().'.pdf';
        Storage::disk('public')->put($path, $bytes);

        return $path;
    }

    public function generateAndStoreInvoice(Order $order): string
    {
        $bytes = $this->generatePdf($order, 'invoice');
        $path = 'invoices/invoice_'.$order->order_number.'_'.time().'.pdf';
        Storage::disk('public')->put($path, $bytes);

        return $path;
    }

    public function generatePdf(Order $order, string $templateKey): string
    {
        $strategy = config("pdf_templates.templates.$templateKey.strategy", 'overlay');
        if ($strategy === 'acroform') {
            $acro = $this->tryAcroform($order, $templateKey);
            if ($acro !== null) {
                return $acro;
            }
        }

        return $this->generateOverlayPdf($order, $templateKey);
    }

    /**
     * @return null|non-empty-string
     */
    protected function tryAcroform(Order $order, string $templateKey): ?string
    {
        $map = config("pdf_templates.templates.$templateKey.acroform_field_map", []);
        if ($map === [] || ! is_array($map)) {
            return null;
        }

        $path = config("pdf_templates.templates.$templateKey.path");
        if (! is_string($path) || ! is_readable($path)) {
            return null;
        }

        $binary = (string) config('pdf_templates.pdftk_binary', 'pdftk');
        $tmpOut = tempnam(sys_get_temp_dir(), 'sw_pdf_');
        if ($tmpOut === false) {
            return null;
        }

        try {
            $pdf = new PdftkPdf($path, ['command' => $binary]);
            $data = [];
            foreach ($map as $internalKey => $pdfFieldName) {
                $val = $this->acroformValue($order, (string) $internalKey);
                if ($val !== null && $pdfFieldName !== '') {
                    $data[(string) $pdfFieldName] = $val;
                }
            }
            if ($data === []) {
                return null;
            }

            $pdf->fillForm($data)->flatten()->saveAs($tmpOut);
            $content = file_get_contents($tmpOut);
            if ($content === false || $content === '') {
                Log::warning('BrandedPdfService: pdftk produced empty output', [
                    'template' => $templateKey,
                    'error' => $pdf->getError(),
                ]);

                return null;
            }

            return $content;
        } catch (\Throwable $e) {
            Log::warning('BrandedPdfService: acroform fill failed, falling back to overlay', [
                'template' => $templateKey,
                'message' => $e->getMessage(),
            ]);

            return null;
        } finally {
            if (is_file($tmpOut)) {
                @unlink($tmpOut);
            }
        }
    }

    protected function acroformValue(Order $order, string $internalKey): ?string
    {
        $order->loadMissing('items', 'customer');

        return match ($internalKey) {
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'customer_company_name' => (string) ($order->customer_company_name ?? ''),
            'customer_vat_number' => (string) ($order->customer_vat_number ?? ''),
            'customer_tax_code' => (string) ($order->customer_tax_code ?? ''),
            'quote_document_display' => (string) ($order->formattedQuoteDocumentReference() ?? ''),
            'invoice_document_display' => (string) ($order->formattedInvoiceDocumentReference() ?? ''),
            'seller_legal_name' => (string) (Setting::get('company_legal_name', Setting::get('site_name', ''))),
            'seller_iban' => (string) (Setting::get('company_iban', '')),
            'seller_rea' => (string) (Setting::get('company_rea', '')),
            'subtotal' => $this->moneyPlain($order->subtotal),
            'shipping_cost' => $this->moneyPlain($order->shipping_cost),
            'tax' => $this->moneyPlain($order->tax),
            'discount_amount' => $this->moneyPlain($order->discount_amount),
            'total' => $this->moneyPlain($order->total),
            'notes' => $order->notes,
            'buy_buy_dept' => (string) ($order->buy_buy_dept ?? ''),
            'shipping_address' => $this->formatAddressBlock($order),
            'created_at' => $order->created_at?->format('d/m/Y H:i'),
            'quote_expires_at' => $order->quote_expires_at?->format('d/m/Y'),
            default => null,
        };
    }

    protected function moneyPlain(mixed $value): string
    {
        return number_format((float) $value, 2, ',', '.');
    }

    protected function generateOverlayPdf(Order $order, string $templateKey): string
    {
        if ($templateKey === 'quote') {
            $order = Order::query()->with(['items' => fn ($q) => $q->orderBy('id')])->findOrFail($order->id);
        }

        $cfg = config("pdf_templates.templates.$templateKey", []);
        $path = $cfg['path'] ?? null;
        if (! is_string($path) || ! is_readable($path)) {
            throw new \RuntimeException("PDF template not readable for [{$templateKey}]");
        }

        $overlay = is_array($cfg['overlay'] ?? null) ? $cfg['overlay'] : [];

        $pdf = new Fpdi;
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(0, 0, 0);
        $pdf->SetAutoPageBreak(false);
        $pdf->SetCreator('SweetHome');

        $pageCount = $pdf->setSourceFile($path);
        $fontSize = (float) ($overlay['font_size'] ?? 8);
        $lineHeight = (float) ($overlay['line_height'] ?? 3.8);

        $body = $this->buildDocumentBody($order, $templateKey);

        for ($i = 1; $i <= $pageCount; $i++) {
            $pdf->AddPage();
            $tpl = $pdf->importPage($i);
            $pdf->useTemplate($tpl);

            if (($overlay['mode'] ?? null) === 'positioned') {
                $this->drawPositionedOverlay($pdf, $order, $overlay, $i, $fontSize, $lineHeight, $templateKey);
            } elseif ($i === (int) ($overlay['content_start_page'] ?? 1)) {
                $pdf->SetFont('dejavusans', '', $fontSize);
                $pdf->SetTextColor(40, 40, 40);
                $pdf->MultiCell(
                    (float) ($overlay['content_width'] ?? 175),
                    $lineHeight,
                    $body,
                    0,
                    'L',
                    false,
                    1,
                    (float) ($overlay['margin_x'] ?? 18),
                    (float) ($overlay['margin_y'] ?? 52),
                    true,
                    0,
                    false,
                    true,
                    0,
                    'T',
                    false
                );
            }
        }

        return $pdf->Output('', 'S');
    }

    protected function drawPositionedOverlay(Fpdi $pdf, Order $order, array $overlay, int $page, float $fontSize, float $lineHeight, string $templateKey): void
    {
        $order->loadMissing('items', 'customer');

        foreach (($overlay['clear_regions'] ?? []) as $region) {
            if (($region['page'] ?? 1) !== $page) {
                continue;
            }
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Rect(
                (float) ($region['x'] ?? 0),
                (float) ($region['y'] ?? 0),
                (float) ($region['w'] ?? 10),
                (float) ($region['h'] ?? 5),
                'F'
            );
        }

        $pdf->SetFont('dejavusans', '', $fontSize);
        $pdf->SetTextColor(25, 25, 25);

        $quoteRef = $order->formattedQuoteDocumentReference();
        $invoiceRef = $order->formattedInvoiceDocumentReference();
        $quoteRefCompact = $quoteRef ? str_replace(' ', '', (string) $quoteRef) : '';
        $quoteCity = (string) config('pdf_templates.templates.quote.quote_document_city', 'Milano');
        $created = $order->created_at;

        $emailPhone = array_filter([
            $order->customer_email !== '' && $order->customer_email !== null ? 'Email: '.$order->customer_email : null,
            $order->customer_phone !== '' && $order->customer_phone !== null ? 'Tel: '.$order->customer_phone : null,
        ]);
        $customerEmailAndPhone = $emailPhone !== [] ? implode('   ', $emailPhone) : '';
        $shippingCompact = $templateKey === 'quote'
            ? $this->formatAddressOneLine($order, 95)
            : $this->formatAddressBlock($order);

        $values = [
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'customer_email_and_phone' => $customerEmailAndPhone,
            'customer_company_name' => (string) ($order->customer_company_name ?? ''),
            'customer_vat_number' => (string) ($order->customer_vat_number ?? ''),
            'customer_tax_code' => (string) ($order->customer_tax_code ?? ''),
            'shipping_address' => $shippingCompact,
            'notes' => $order->notes,
            'subtotal' => $this->money($order->subtotal),
            'total' => $this->money($order->total),
            'order_number' => (string) $order->order_number,
            'quote_created_at' => $created?->format('d/m/Y H:i') ?? '',
            'quote_riferimento' => 'Riferimento: '.(string) $order->order_number,
            'quote_data_ora' => 'Data: '.($created?->format('d/m/Y H:i') ?? ''),
            'quote_meta_line' => 'Riferimento: '.(string) $order->order_number.'    Data: '.($created?->format('d/m/Y H:i') ?? ''),
            'quote_place_date' => $created ? "{$quoteCity}: ".$created->format('d.m.Y') : '',
            'quote_header_right' => $templateKey === 'quote' && $quoteRefCompact !== ''
                ? 'N. preventivo: '.$quoteRefCompact
                : '',
            'quote_document_display' => $templateKey === 'quote' ? $quoteRefCompact : '',
            'invoice_document_display' => $templateKey === 'invoice' ? (string) ($invoiceRef ?? '') : '',
            'seller_legal_name' => (string) (Setting::get('company_legal_name', Setting::get('site_name', ''))),
            'seller_vat_number' => (string) (Setting::get('vat_number', '')),
            'seller_tax_code' => (string) (Setting::get('tax_code', '')),
            'seller_address' => (string) (Setting::get('site_address', '')),
            'seller_iban' => (string) (Setting::get('company_iban', '')),
            'seller_rea' => (string) (Setting::get('company_rea', '')),
            'seller_pec' => (string) (Setting::get('company_pec', '')),
            'seller_sdi' => (string) (Setting::get('company_sdi', '')),
        ];

        foreach (($overlay['fields'] ?? []) as $key => $field) {
            if (($field['page'] ?? 1) !== $page) {
                continue;
            }

            $sourceKey = (string) ($field['value_from'] ?? $key);
            if (! array_key_exists($sourceKey, $values)) {
                continue;
            }

            $text = (string) $values[$sourceKey];
            if (($field['skip_if_blank'] ?? true) && blank($text)) {
                continue;
            }

            $fs = $field['font_size'] ?? null;
            if ($fs !== null) {
                $pdf->SetFont('dejavusans', '', (float) $fs);
            }

            $this->drawCell($pdf, $text, $field, $lineHeight, (string) ($field['align'] ?? 'L'));

            if ($fs !== null) {
                $pdf->SetFont('dejavusans', '', $fontSize);
            }
        }

        foreach (($overlay['item_rows'] ?? []) as $index => $row) {
            if (($row['page'] ?? 1) !== $page) {
                continue;
            }

            $item = $order->items->values()->get($index);
            if (! $item) {
                continue;
            }

            $pdf->SetFont('dejavusans', '', $fontSize);
            $pdf->SetTextColor(25, 25, 25);

            $item->loadMissing('product');
            $description = trim((string) ($item->line_description ?: $item->product_name));
            if ($description === '' && $item->product !== null) {
                $description = trim((string) $item->product->name);
            }
            if ($description === '') {
                $description = 'Articolo #'.$item->id;
            }

            $this->drawCell($pdf, $description, [
                'x' => $row['description_x'],
                'y' => $row['y'],
                'w' => $row['description_w'],
                'h' => $lineHeight,
            ], $lineHeight);

            $this->drawCell($pdf, $this->moneyPlain($item->unit_price), [
                'x' => $row['price_x'],
                'y' => $row['y'],
                'w' => $row['price_w'],
                'h' => $lineHeight,
                'single_line' => true,
            ], $lineHeight, 'R');

            if ($row['qty_x'] !== null) {
                $this->drawCell($pdf, (string) $item->quantity, [
                    'x' => $row['qty_x'],
                    'y' => $row['y'],
                    'w' => $row['qty_w'],
                    'h' => $lineHeight,
                    'single_line' => true,
                ], $lineHeight, 'C');
            }

            if ($row['total_x'] !== null) {
                $this->drawCell($pdf, $this->moneyPlain($item->total_price), [
                    'x' => $row['total_x'],
                    'y' => $row['y'],
                    'w' => $row['total_w'],
                    'h' => $lineHeight,
                    'single_line' => true,
                ], $lineHeight, 'R');
            }
        }

        foreach (($overlay['totals'] ?? []) as $key => $field) {
            if (($field['page'] ?? 1) !== $page || ! isset($values[$key])) {
                continue;
            }

            $totField = $field;
            $totField['single_line'] = $totField['single_line'] ?? true;
            $this->drawCell($pdf, (string) $values[$key], $totField, $lineHeight, (string) ($totField['align'] ?? 'R'));
        }
    }

    protected function drawCell(Fpdi $pdf, string $text, array $field, float $lineHeight, string $align = 'L'): void
    {
        $text = preg_replace('/[\x{200E}\x{200F}\x{202A}\x{202C}]/u', '', $text) ?? $text;

        $x = (float) ($field['x'] ?? 0);
        $y = (float) ($field['y'] ?? 0);
        $w = (float) ($field['w'] ?? 40);
        $h = (float) ($field['h'] ?? $lineHeight);

        if (! empty($field['background'])) {
            $pdf->SetFillColor(255, 255, 255);
            $pdf->Rect($x, $y, $w, $h, 'F');
        }

        if (! empty($field['single_line'])) {
            $pdf->SetXY($x, $y);
            // stretch must stay 0: stretch=1/2 scales text to fill $w and spreads it across the page (collides with centered title).
            $stretch = (int) ($field['cell_stretch'] ?? 0);
            $pdf->Cell($w, $h, $text, 0, 0, $align, false, '', $stretch);

            return;
        }

        $pdf->MultiCell(
            $w,
            $h,
            $text,
            0,
            $align,
            false,
            1,
            $x,
            $y,
            true,
            0,
            false,
            true,
            $h,
            'T',
            false
        );
    }

    protected function buildDocumentBody(Order $order, string $templateKey): string
    {
        $order->loadMissing('items', 'customer');

        $title = match ($templateKey) {
            'quote' => 'DETTAGLI PREVENTIVO',
            'order_confirmation' => 'DETTAGLI ORDINE',
            'invoice' => 'DATI DOCUMENTO COMMERCIALE',
            default => 'DOCUMENTO',
        };

        $lines = [];
        $lines[] = $title;
        $lines[] = str_repeat('─', 56);
        $lines[] = 'Riferimento interno: '.$order->order_number;
        if ($templateKey === 'quote' && $order->formattedQuoteDocumentReference()) {
            $lines[] = 'Preventivo n.: '.$order->formattedQuoteDocumentReference();
        }
        if ($templateKey === 'invoice' && $order->formattedInvoiceDocumentReference()) {
            $lines[] = 'Fattura n.: '.$order->formattedInvoiceDocumentReference();
        }
        $lines[] = 'Data: '.($order->created_at?->format('d/m/Y H:i') ?? '');
        if ($templateKey === 'quote' && $order->quote_expires_at) {
            $lines[] = 'Valido fino al: '.$order->quote_expires_at->format('d/m/Y');
        }
        if ($templateKey === 'invoice') {
            $lines[] = 'Documento emesso il: '.now()->format('d/m/Y H:i');
            $lines[] = 'Stato pagamento: '.($order->payment_status ?? '');
            if ($order->payment_method) {
                $lines[] = 'Metodo: '.$order->payment_method;
            }
            $sellerName = (string) (Setting::get('company_legal_name', Setting::get('site_name', '')));
            if ($sellerName !== '') {
                $lines[] = 'Fornitore: '.$sellerName;
            }
            $sellerVat = (string) Setting::get('vat_number', '');
            if ($sellerVat !== '') {
                $lines[] = 'P.IVA fornitore: '.$sellerVat;
            }
            $iban = (string) Setting::get('company_iban', '');
            if ($iban !== '') {
                $lines[] = 'IBAN: '.$iban;
            }
        }
        $lines[] = '';
        $lines[] = 'Cliente: '.$order->customer_name;
        if ($order->customer_company_name) {
            $lines[] = 'Ragione sociale: '.$order->customer_company_name;
        }
        $lines[] = 'Email: '.$order->customer_email;
        if ($order->customer_phone) {
            $lines[] = 'Tel: '.$order->customer_phone;
        }
        if ($order->customer_vat_number) {
            $lines[] = 'P.IVA cliente: '.$order->customer_vat_number;
        }
        if ($order->customer_tax_code) {
            $lines[] = 'Codice fiscale: '.$order->customer_tax_code;
        }
        $lines[] = '';
        $lines[] = 'Indirizzo di spedizione:';
        $lines[] = $this->formatAddressBlock($order);
        $lines[] = '';
        $lines[] = 'Articoli:';
        foreach ($order->items as $item) {
            $desc = $item->line_description ?: $item->product_name;
            $vatSuffix = $item->vat_rate !== null ? '  IVA '.number_format((float) $item->vat_rate, 2, ',', '').'%' : '';
            $lines[] = sprintf(
                ' • %s  x%s  %s  = %s%s',
                $desc,
                $item->quantity,
                $this->money($item->unit_price),
                $this->money($item->total_price),
                $vatSuffix
            );
        }
        $lines[] = '';
        $lines[] = 'Subtotale: '.$this->money($order->subtotal);
        if ((float) $order->shipping_cost > 0) {
            $lines[] = 'Spedizione: '.$this->money($order->shipping_cost);
        }
        if ((float) $order->tax > 0) {
            $lines[] = 'IVA: '.$this->money($order->tax);
        }
        if ((float) $order->discount_amount > 0) {
            $lines[] = 'Sconto: -'.$this->money($order->discount_amount);
        }
        $lines[] = 'TOTALE: '.$this->money($order->total);
        if ($order->notes) {
            $lines[] = '';
            $lines[] = 'Note:';
            $lines[] = $order->notes;
        }

        return implode("\n", $lines);
    }

    /**
     * Shorten multi-line addresses so overlay fits the modulo band above the centered title (~85 mm).
     */
    protected function formatAddressBlockCompact(Order $order, int $maxLines = 2): string
    {
        $full = $this->formatAddressBlock($order);
        $lines = array_values(array_filter(explode("\n", $full), fn ($l) => trim((string) $l) !== ''));
        if (count($lines) <= $maxLines) {
            return $full;
        }
        $head = array_slice($lines, 0, $maxLines - 1);
        $tail = implode(', ', array_slice($lines, $maxLines - 1));
        $head[] = $tail;

        return implode("\n", $head);
    }

    /**
     * Single-line address for modulo layout (Cliente / Indirizzo row + title band).
     */
    protected function formatAddressOneLine(Order $order, int $maxChars = 90): string
    {
        $flat = preg_replace('/\s+/', ' ', str_replace(["\n", "\r"], ', ', $this->formatAddressBlock($order)));
        $flat = trim((string) $flat);

        return mb_strlen($flat) > $maxChars ? mb_substr($flat, 0, $maxChars - 1).'…' : $flat;
    }

    protected function formatAddressBlock(Order $order): string
    {
        $a = $order->shipping_address;
        if (! is_array($a)) {
            return (string) $a;
        }
        if (isset($a['line1'])) {
            $parts = array_filter([
                $a['line1'] ?? '',
                $a['line2'] ?? '',
                trim(($a['postal_code'] ?? '').' '.($a['city'] ?? '').' '.($a['province'] ?? '')),
                $a['country'] ?? '',
            ]);

            return implode("\n", $parts);
        }
        $parts = array_filter([
            $a['address'] ?? '',
            trim(($a['zip_code'] ?? '').' '.($a['city'] ?? '').' '.($a['state'] ?? '')),
            $a['country'] ?? '',
        ]);

        return implode("\n", $parts);
    }

    protected function money(mixed $value): string
    {
        return '€ '.number_format((float) $value, 2, ',', '.');
    }
}
