<?php

namespace App\Models\Sma\Hr;

use App\Models\Model;

class Payslip extends Model
{
    protected $with = ['employee.user:id,name', 'payroll:id,title,month,year,status'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function items()
    {
        return $this->hasMany(PayslipItem::class);
    }

    public function allowances()
    {
        return $this->hasMany(PayslipItem::class)->where('type', 'allowance');
    }

    public function deductions()
    {
        return $this->hasMany(PayslipItem::class)->where('type', 'deduction');
    }
}
