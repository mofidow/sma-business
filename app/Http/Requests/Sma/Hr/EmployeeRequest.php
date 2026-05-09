<?php

namespace App\Http\Requests\Sma\Hr;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('employee') ? 'update-employees' : 'create-employees');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'user_id'                => 'required|exists:users,id',
            'store_id'               => 'required|exists:stores,id',
            'department'             => 'nullable|string|max:255',
            'job_title'              => 'nullable|string|max:255',
            'hire_date'              => 'nullable|date',
            'basic_salary'           => 'required|numeric|min:0',
            'working_days_per_month' => 'required|integer|min:1|max:31',
            'working_hours_per_day'  => 'required|integer|min:1|max:24',
            'notes'                  => 'nullable|string',
        ];
    }
}
