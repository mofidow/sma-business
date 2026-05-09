<?php

namespace App\Tec\Exports;

use App\Models\Sma\Product\Category;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class CategoryExport extends StringValueBinder implements FromCollection, ShouldAutoSize, WithCustomValueBinder, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(public bool $template = false) {}

    public function collection()
    {
        if ($this->template) {
            return collect([new Category()]);
        }

        return Category::all();
    }

    public function headings(): array
    {
        return ['name', 'slug', 'order', 'photo', 'parent_category', 'active', 'title', 'description'];
    }

    public function map($category): array
    {
        $category->parent_category = $category->category_id ? Category::find($category->category_id)?->name : null;

        return [
            'name'            => $category->name,
            'slug'            => $category->slug,
            'order'           => $category->order,
            'photo'           => $category->photo,
            'parent_category' => $category->parent_category,
            'active'          => $category->active,
            'title'           => $category->title,
            'description'     => $category->description,
        ];
    }
}
