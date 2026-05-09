<?php

namespace App\Tec\Imports;

use App\Models\User;
use App\Models\Sma\Product\Brand;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Concerns\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use App\Http\Requests\Sma\Product\BrandRequest;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Tec\Notifications\Import\ImportHasFailedNotification;

class BrandImport implements ShouldQueue, ToModel, WithBatchInserts, WithChunkReading, WithEvents, WithHeadingRow, WithUpserts, WithValidation
{
    use Importable;

    public function __construct(public User $user) {}

    public function model(array $row)
    {
        if (! ($row['slug'] ?? null)) {
            $row['slug'] = str($row['name'])->slug();
        }

        return new Brand([
            'name'        => $row['name'],
            'slug'        => $row['slug'],
            'order'       => $row['order'],
            'photo'       => $row['photo'],
            'active'      => $row['active'],
            'title'       => $row['title'],
            'description' => $row['description'],
        ]);
    }

    public function rules(): array
    {
        $rules = (new BrandRequest)->rules();
        $rules['name'] = 'required';
        $rules['slug'] = 'nullable';
        $rules['photo'] = 'nullable|url';

        return $rules;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        return 'name';
    }

    public function registerEvents(): array
    {
        return [
            ImportFailed::class => function (ImportFailed $event) {
                logger()->error('Import failed with error: ' . $event->getException()->getMessage(), [
                    'exception' => $event->getException(),
                ]);
                $this->user->notify(new ImportHasFailedNotification($event, 'An error occurred while importing brands. Please check the error message below and for more details please check the latest log file in the storage/logs directory.'));
            },
        ];
    }
}
