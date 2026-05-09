<?php

namespace App\Tec\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Sma\Order\PurchaseItem;

class InitializePurchaseBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'costing:initialize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize purchase item balances for existing data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = PurchaseItem::query()->whereNull('balance')->count();

        if ($count === 0) {
            $this->info('All purchase items already have balances set.');

            return self::SUCCESS;
        }

        $this->info("Found {$count} purchase items with null balances.");

        PurchaseItem::query()
            ->whereNull('balance')
            ->update(['balance' => DB::raw('base_quantity')]);

        DB::table('purchase_item_variation')
            ->whereNull('balance')
            ->update(['balance' => DB::raw('base_quantity')]);

        $this->info('Purchase item balances initialized successfully.');

        return self::SUCCESS;
    }
}
