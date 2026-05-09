<?php

namespace App\Tec\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ComboProductImport implements ToArray, WithBatchInserts, WithChunkReading, WithHeadingRow, WithUpserts
{
    use Importable;

    public function array(array $row)
    {
        if (($row['main_product_code'] ?? null) && ($row['combo_product_code'] ?? null) && ($row['quantity'] ?? null)) {
            $productId = Product::select('id')->where('code', $row['main_product_code'])->first()?->id;
            $comboProductId = Product::where('code', $row['combo_product_code'])->first()?->id;

            return ['combo_id' => $productId, 'product_id' => $comboProductId, 'quantity' => $row['quantity']];
        }
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
        return ['combo_id', 'product_id'];
    }
}
