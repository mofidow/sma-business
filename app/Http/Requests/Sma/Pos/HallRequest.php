<?php

namespace App\Http\Requests\Sma\Pos;

use App\Models\Sma\Pos\Hall;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class HallRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('hall') ? 'update-halls' : 'create-halls');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'active'      => 'nullable|boolean',
            'sort_order'  => 'nullable|integer|min:0',
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

        if (Hall::where('name', $data['name'])->exists()) {
            if ($this->route('hall')?->id && $this->route('hall')->name === $data['name']) {
                return $data;
            }
            throw ValidationException::withMessages([
                'name' => __('The name has already been taken.'),
            ]);
        }

        return $data;
    }
}
