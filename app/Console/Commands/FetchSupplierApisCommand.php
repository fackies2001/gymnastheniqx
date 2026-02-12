<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Suppliers\SupplierFetcherService;

class FetchSupplierApisCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-supplier-apis-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all supplier APIs and store products';

    public function handle(SupplierFetcherService $fetcherService)
    {
        $this->info('Fetching Supplier APIs...');
        $fetcherService->fetchAllSuppliers();
        $this->info('Fetch Completed Successfully!');
    }
}
