<?php

namespace App\Http\Requests\Sma\Pos;

use App\Models\Sma\Pos\Table;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class TableRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('table') ? 'update-tables' : 'create-tables');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hall_id'     => 'required|exists:halls,id',
            'name'        => 'required|string|max:255',
            'seats'       => 'required|integer|min:1|max:100',
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

        if (Table::where('name', $data['name'])->where('hall_id', $data['hall_id'])->exists()) {
            if ($this->route('table')?->id && $this->route('table')->name === $data['name'] && $this->route('table')->hall_id === $data['hall_id']) {
                return $data;
            }
            throw ValidationException::withMessages([
                'name' => __('The name has already been taken.'),
            ]);
        }

        return $data;
    }
}
