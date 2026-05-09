<?php

namespace App\Http\Requests\Sma\Product;

use App\Tec\Rules\ExtraAttributes;
use Maatwebsite\Excel\Facades\Excel;
use App\Tec\Imports\StockCountImport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Validators\ValidationException as ExcelException;

class StockCountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('stock_count') ? 'update-stock-counts' : 'create-stock-counts');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date'      => 'required|date',
            'type'      => 'required|in:full,partial',
            'reference' => 'nullable|max:50|unique:stock_counts,reference,' . $this->route('stock_count')?->id,

            'details'       => 'nullable',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'mimes:' . ((get_settings('attachment_exts') ?? null) ?: 'jpg,png,pdf,xlsx,docx,zip'),

            'brands'     => 'nullable|array',
            'categories' => 'nullable|array',
            'file'       => 'nullable|mimes:xlsx',

            'extra_attributes' => new ExtraAttributes('stock_count'),
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @param  array|int|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if ($data['type'] == 'partial' && ! ($data['brands'] ?? null) && ! ($data['categories'] ?? null)) {
            throw ValidationException::withMessages([
                'brands' => __('At least one brand or category should be selected for partial stock count.'),
            ]);
        }

        if ($this->has('file') && $this->file) {
            try {
                Excel::import(new StockCountImport($this->route('stock_count')), $this->file);
            } catch (ExcelException $e) {
                $errors = [];
                $failures = $e->failures();
                foreach ($failures as $failure) {
                    $errors['rows.' . $failure->row() . '.' . $failure->attribute()] = $failure->errors();
                }
                throw ValidationException::withMessages($errors);
            }

            $data['file'] = $this->file->store('/stock_counts', 'local');
            $data['completed_at'] = now();
            $data['completed_by'] = auth()->id();

            unset($data['date'], $data['type'], $data['reference'], $data['brands'], $data['categories'], $data['details'], $data['attachments'], $data['extra_attributes']);
        } else {
            unset($data['file']);
        }

        return $data;
    }
}
