<?php

namespace App\Console\Commands;

use App\Models\Finance\Invoice;
use App\Services\Finance\InvoiceService;
use Illuminate\Console\Command;

class UpdateInvoiceAmounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoice:update-amounts {--batch=100 : Process invoices in batches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update invoiceAmount and ppnAmount for all invoices by recalculating from details';

    /**
     * Create a new command instance.
     */
    public function __construct(private InvoiceService $invoiceService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $batchSize = (int) $this->option('batch');

        // Get total count
        $totalInvoices = Invoice::count();

        if ($totalInvoices === 0) {
            $this->info('No invoices found to update.');

            return Command::SUCCESS;
        }

        $this->info("Found {$totalInvoices} invoices to update.");
        $this->info("Processing in batches of {$batchSize}...\n");

        $processed = 0;
        $errors = 0;

        // Process in batches
        $invoices = Invoice::with('details.order.cost', 'customer')->cursor();

        $bar = $this->output->createProgressBar($totalInvoices);
        $bar->start();

        foreach ($invoices as $invoice) {
            try {
                $totals = $this->invoiceService->calculateInvoiceAmount($invoice);

                $invoice->update([
                    'invoiceAmount' => $totals['subtotal'],
                    'routeAmount' => $totals['routeTotal'],
                    'onChargeAmount' => $totals['onChargeTotal'],
                    'ppnAmount' => $totals['ppn'],
                    'pphAmount' => $totals['pph'],
                    'pphBaseAmount' => $totals['pphBaseAmount'],
                ]);

                $this->invoiceService->synchronizePaymentStatus($invoice, (float) $totals['total']);

                $processed++;
            } catch (\Exception $e) {
                $errors++;
                $this->error("\nError updating invoice {$invoice->code}: ".$e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();

        $this->newLine(2);
        $this->info('Update completed!');
        $this->info("✓ Processed: {$processed}");
        $this->info("✗ Errors: {$errors}");

        return Command::SUCCESS;
    }
}
