<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\QuoteService;
use Illuminate\Console\Command;

class RegenerateQuotePdfs extends Command
{
    protected $signature = 'quotes:regenerate-pdfs';

    protected $description = 'Regenerate PDFs for all existing quote requests';

    public function handle(QuoteService $quoteService)
    {
        $quotes = Order::where('type', 'quote_request')->where('status', '!=', 'cancelled')->get();

        if ($quotes->isEmpty()) {
            $this->info('No quote requests found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$quotes->count()} quote requests. Regenerating PDFs...");

        $successCount = 0;
        $failCount = 0;

        foreach ($quotes as $quote) {
            try {
                $quote->load('items');
                $quoteService->generatePdf($quote, advanceWorkflow: false);
                $successCount++;
                $this->info("✓ Generated PDF for {$quote->order_number}");
            } catch (\Exception $e) {
                $failCount++;
                $this->error("✗ Failed to generate PDF for {$quote->order_number}: {$e->getMessage()}");
            }
        }

        $this->info("\nCompleted: {$successCount} successful, {$failCount} failed");

        return Command::SUCCESS;
    }
}
