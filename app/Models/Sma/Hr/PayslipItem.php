<?php

namespace App\Models\Sma\Hr;

use Illuminate\Database\Eloquent\Model;

class PayslipItem extends Model
{
    protected $guarded = [];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }
}
