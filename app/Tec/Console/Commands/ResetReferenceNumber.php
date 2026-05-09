<?php

namespace App\Tec\Console\Commands;

use App\Models\Setting;
use App\Models\Sma\Order\Sale;
use Illuminate\Console\Command;
use App\Models\Sma\Order\Expense;
use App\Models\Sma\Order\Payment;
use App\Models\Sma\Setting\Store;
use App\Models\Sma\Order\Delivery;
use App\Models\Sma\Order\Purchase;
use App\Models\Sma\Order\Quotation;
use App\Models\Sma\Product\Transfer;
use App\Models\Sma\Order\ReturnOrder;
use App\Models\Sma\Product\Adjustment;
use App\Models\Sma\Product\StockCount;

class ResetReferenceNumber extends Command
{
    protected $signature = 'app:reset-reference-number';

    protected $description = 'Reset reference numbers based on reference format (yearly or monthly)';

    protected array $models = [
        Sale::class,
        Purchase::class,
        Quotation::class,
        Payment::class,
        Expense::class,
        Delivery::class,
        ReturnOrder::class,
        Transfer::class,
        Adjustment::class,
        StockCount::class,
    ];

    public function handle(): int
    {
        $format = get_settings('reference');

        if (! $format) {
            $this->info('No reference format configured.');

            return self::SUCCESS;
        }

        $yearlyFormats = ['Y', 'Y/', 'Y-'];
        $monthlyFormats = ['Ym', 'Y/m/', 'Y-m-'];

        $shouldReset = match (true) {
            in_array($format, $yearlyFormats) && now()->startOfYear()->isToday()   => 'yearly',
            in_array($format, $monthlyFormats) && now()->startOfMonth()->isToday() => 'monthly',
            default                                                                => null,
        };

        if (! $shouldReset) {
            $this->info("No reset needed for format '{$format}' on " . now()->toDateString());

            return self::SUCCESS;
        }

        $this->info("Resetting {$shouldReset} reference numbers for format '{$format}'");

        $this->resetSettingsReferences();
        $this->resetStoreReferences();

        $this->info('Reference numbers have been reset successfully.');

        return self::SUCCESS;
    }

    protected function resetSettingsReferences(): void
    {
        $this->line('Resetting settings references...');

        foreach ($this->models as $modelClass) {
            $key = $this->getReferenceKey($modelClass);
            Setting::where('tec_key', $key)->update(['tec_value' => 0]);
            $this->line("  - Reset {$key}");
        }
    }

    protected function resetStoreReferences(): void
    {
        if (! get_settings('reference_per_store')) {
            return;
        }

        $this->line('Resetting store references...');

        $stores = Store::all();
        foreach ($stores as $store) {
            $references = $store->references ?? (object) [];

            foreach ($this->models as $modelClass) {
                $key = $this->getReferenceKey($modelClass);
                $references->{$key} = 0;
            }

            $store->update(['references' => $references]);
            $this->line("  - Reset references for store: {$store->name}");
        }
    }

    protected function getReferenceKey(string $modelClass): string
    {
        return str($modelClass)
            ->replace('App\\Models\\', '')
            ->snake()->append('_reference')->toString();
    }
}
