<?php

namespace App\Services;

use App\Models\DocumentSequence;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Assign sequential quote document number/year when both are missing.
     * Admin override: set both quote_document_number and quote_document_year on the order (skipped here).
     */
    public function ensureQuoteDocumentAssigned(Order $order): void
    {
        if (! $order->isQuoteRequest()) {
            return;
        }

        if ($order->quote_document_number !== null && $order->quote_document_year !== null) {
            return;
        }

        if ($order->quote_document_number !== null && $order->quote_document_year === null) {
            $order->update(['quote_document_year' => (int) now()->year]);

            return;
        }

        if ($order->quote_document_number === null && $order->quote_document_year !== null) {
            $this->assignNextForYear($order, 'quote', (int) $order->quote_document_year);

            return;
        }

        $this->assignNext($order, 'quote');
    }

    /**
     * Assign sequential invoice document number/year when both are missing (direct orders).
     */
    public function ensureInvoiceDocumentAssigned(Order $order): void
    {
        if ($order->type !== 'direct') {
            return;
        }

        if ($order->invoice_document_number !== null && $order->invoice_document_year !== null) {
            return;
        }

        if ($order->invoice_document_number !== null && $order->invoice_document_year === null) {
            $order->update(['invoice_document_year' => (int) now()->year]);

            return;
        }

        if ($order->invoice_document_number === null && $order->invoice_document_year !== null) {
            $this->assignNextForYear($order, 'invoice', (int) $order->invoice_document_year);

            return;
        }

        $this->assignNext($order, 'invoice');
    }

    protected function assignNext(Order $order, string $type): void
    {
        $this->assignNextForYear($order, $type, (int) now()->year);
    }

    protected function assignNextForYear(Order $order, string $type, int $year): void
    {
        DB::transaction(function () use ($order, $type, $year): void {
            $seq = DocumentSequence::query()
                ->where('type', $type)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if (! $seq) {
                $seq = DocumentSequence::create([
                    'type' => $type,
                    'year' => $year,
                    'last_number' => 0,
                ]);
            }

            $seq->increment('last_number');
            $seq->refresh();

            if ($type === 'quote') {
                $order->update([
                    'quote_document_number' => $seq->last_number,
                    'quote_document_year' => $year,
                ]);
            } else {
                $order->update([
                    'invoice_document_number' => $seq->last_number,
                    'invoice_document_year' => $year,
                ]);
            }
        });
    }
}
