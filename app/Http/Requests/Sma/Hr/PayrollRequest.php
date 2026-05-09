<?php

namespace App\Http\Requests\Sma\Hr;

use App\Models\Sma\Hr\Payroll;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class PayrollRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('payroll') ? 'update-payrolls' : 'create-payrolls');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'store_id' => 'required|exists:stores,id',
            'title'    => 'required|string|max:255',
            'month'    => 'required|integer|min:1|max:12',
            'year'     => 'required|integer|min:2020',
            'notes'    => 'nullable|string',

            'payslips'                          => 'nullable|array',
            'payslips.*.employee_id'            => 'required|exists:employees,id',
            'payslips.*.basic_salary'           => 'required|numeric|min:0',
            'payslips.*.working_days'           => 'required|integer|min:0',
            'payslips.*.present_days'           => 'required|integer|min:0',
            'payslips.*.absent_days'            => 'required|integer|min:0',
            'payslips.*.on_leave_days_paid'     => 'required|integer|min:0',
            'payslips.*.on_leave_days_unpaid'   => 'required|integer|min:0',
            'payslips.*.overtime_hours'         => 'required|numeric|min:0',
            'payslips.*.overtime_amount'        => 'required|numeric|min:0',
            'payslips.*.gross_salary'           => 'required|numeric|min:0',
            'payslips.*.overtime_rate'          => 'nullable|numeric|min:0',
            'payslips.*.absent_deduction'       => 'nullable|numeric|min:0',
            'payslips.*.unpaid_leave_deduction' => 'nullable|numeric|min:0',
            'payslips.*.total_deductions'       => 'required|numeric|min:0',
            'payslips.*.total_allowances'       => 'required|numeric|min:0',
            'payslips.*.net_salary'             => 'required|numeric|min:0',
            'payslips.*.notes'                  => 'nullable|string',
            'payslips.*.items'                  => 'nullable|array',
            'payslips.*.items.*.type'           => 'required|in:allowance,deduction',
            'payslips.*.items.*.description'    => 'required|string',
            'payslips.*.items.*.amount'         => 'required|numeric|min:0',
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

        if (! $this->route('payroll')?->id && Payroll::where('store_id', $data['store_id'])->where('month', $data['month'])->where('year', $data['year'])->exists()) {
            throw ValidationException::withMessages(['month' => __('Payroll for the selected month and year already exists for this store.')]);
        }

        return $data;
    }
}
