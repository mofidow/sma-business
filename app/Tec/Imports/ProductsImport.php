<?php

namespace App\Tec\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\ImportFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsUnknownSheets;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Tec\Notifications\Import\ImportHasFailedNotification;

class ProductsImport implements ShouldQueue, SkipsUnknownSheets, WithChunkReading, WithEvents, WithMultipleSheets
{
    use Importable;

    public function __construct(public User $user) {}

    public function sheets(): array
    {
        return [
            'Products'           => new ProductImport($this->user),
            'Product Taxes'      => new ProductTaxImport($this->user),
            'Product Variations' => new ProductVariationImport($this->user),
            'Combo Products'     => new ComboProductRowImport($this->user),
        ];
    }

    public function onUnknownSheet($sheetName)
    {
        logger()->info("Sheet {$sheetName} was skipped");
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function registerEvents(): array
    {
        return [
            ImportFailed::class => function (ImportFailed $event) {
                logger()->error('Import failed with error: ' . $event->getException()->getMessage(), [
                    'exception' => $event->getException(),
                ]);
                $this->user->notify(new ImportHasFailedNotification($event, 'An error occurred while importing products. Please check the error message below and for more details please check the latest log file in the storage/logs directory.'));
            },
        ];
    }
}
