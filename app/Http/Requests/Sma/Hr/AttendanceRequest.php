<?php

namespace App\Http\Requests\Sma\Hr;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('attendance') ? 'update-attendances' : 'create-attendances');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id'    => 'required|exists:employees,id',
            'store_id'       => 'required|exists:stores,id',
            'date'           => 'required|date',
            'clock_in'       => 'nullable|date_format:H:i',
            'clock_out'      => 'nullable|date_format:H:i|after:clock_in',
            'overtime_hours' => 'nullable|numeric|min:0',
            'status'         => 'required|in:present,absent,late,half_day,holiday,on_leave',
            'note'           => 'nullable|string',
        ];
    }
}
