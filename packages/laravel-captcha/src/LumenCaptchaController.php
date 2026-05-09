<?php

namespace Tec\Captcha;

use Exception;
use Laravel\Lumen\Routing\Controller;

/**
 * Class CaptchaController
 */
class LumenCaptchaController extends Controller
{
    /**
     * get CAPTCHA
     *
     * @param string $config
     * @return array|mixed
     *
     * @throws Exception
     */
    public function getCaptcha(Captcha $captcha, $config = 'default')
    {
        return $captcha->create($config);
    }
}
