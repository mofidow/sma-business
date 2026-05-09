<?php

namespace App\Http\Requests\Sma\Product;

use App\Tec\Rules\ExtraAttributes;
use App\Tec\Rules\ProductVariation;
use App\Tec\Services\CheckOverSelling;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AdjustmentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('adjustment') ? 'update-adjustments' : 'create-adjustments');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type'      => 'required|in:Addition,Subtraction',
            'reference' => 'nullable|max:50|unique:adjustments,reference,' . $this->route('adjustment')?->id,

            'details'       => 'nullable',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'mimes:' . ((get_settings('attachment_exts') ?? null) ?: 'jpg,png,pdf,xlsx,docx,zip'),

            'items'                         => 'required|array|min:1',
            'items.*.id'                    => 'nullable',
            'items.*.quantity'              => 'required|numeric|min:0',
            'items.*.old_quantity'          => 'nullable|numeric',
            'items.*.product_id'            => 'required|exists:products,id',
            'items.*.variations'            => new ProductVariation,
            'items.*.variations.*.id'       => new ProductVariation,
            'items.*.variations.*.quantity' => 'required_with:items.*.variations|numeric|min:0',

            'extra_attributes' => new ExtraAttributes('adjustment'),
        ];
    }

    protected function passedValidation()
    {
        if (! get_settings('overselling') && $this->type == 'Subtraction') {
            $error = (new CheckOverSelling)->check($this->input('items'));
            if ($error) {
                throw ValidationException::withMessages($error);
            }
        }
    }
}
