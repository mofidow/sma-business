<?php

namespace Modules\Shop\Tec;

use Illuminate\Support\Number;

final class Tec
{
    public static function currency($number, $locale = 'en', $precision = 2, $currency = 'USD')
    {
        return Number::currency($number, in: $currency, locale: $locale, precision: $precision);
    }
}
