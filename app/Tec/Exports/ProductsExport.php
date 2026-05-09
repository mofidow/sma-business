<?php

namespace App\Tec\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ProductsExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(public bool $template = false) {}

    public function sheets(): array
    {
        return [
            new ProductExport($this->template),
            new ProductTaxExport($this->template),
            new ProductVariationExport($this->template),
            new ComboProductExport($this->template),
        ];
    }
}
