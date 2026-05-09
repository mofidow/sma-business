<?php

namespace Tec\Captcha\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tec\Captcha\Captcha
 */
class Captcha extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'captcha';
    }
}
