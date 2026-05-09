<?php

namespace App\Http\Requests\Sma\Product;

use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('brand') ? 'update-brands' : 'create-brands');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'photo'       => 'nullable|max:500|mimes:jpg,jpeg,png,avif,webp',
            'name'        => 'required|unique:categories,name,' . $this->route('brand')?->id,
            'slug'        => 'nullable|unique:categories,slug,' . $this->route('brand')?->id,
            'order'       => 'nullable|numeric',
            'title'       => 'nullable|string|max:50',
            'description' => 'nullable|string|max:160',
            'active'      => 'nullable|boolean',
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

        if ($this->has('photo') && $this->photo) {
            $data['photo'] = Storage::disk('asset')->url($this->photo->store('/images', 'asset'));
            if ($this->route('category')?->id && $this->route('category')?->photo) {
                Storage::disk('asset')->delete($this->route('category')->photo);
            }
        } else {
            unset($data['photo']);
        }

        return $data;
    }
}
