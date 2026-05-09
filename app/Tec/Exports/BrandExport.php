<?php

namespace App\Tec\Exports;

use App\Models\Sma\Product\Brand;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class BrandExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(public bool $template = false) {}

    public function collection()
    {
        if ($this->template) {
            return collect([new Brand()]);
        }

        return Brand::all();
    }

    public function headings(): array
    {
        return ['name', 'slug', 'order', 'photo', 'active', 'title', 'description'];
    }

    public function map($brand): array
    {
        return [
            'name'        => $brand->name,
            'slug'        => $brand->slug,
            'order'       => $brand->order,
            'photo'       => $brand->photo,
            'active'      => $brand->active,
            'title'       => $brand->title,
            'description' => $brand->description,
        ];
    }
}
