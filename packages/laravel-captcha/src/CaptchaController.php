<?php

namespace Tec\Captcha;

use Exception;
use Illuminate\Routing\Controller;

/**
 * Class CaptchaController
 */
class CaptchaController extends Controller
{
    /**
     * get CAPTCHA
     *
     * @return array|mixed
     *
     * @throws Exception
     */
    public function getCaptcha(Captcha $captcha, string $config = 'default')
    {
        if (ob_get_contents()) {
            ob_clean();
        }

        return $captcha->create($config);
    }

    /**
     * get CAPTCHA api
     *
     * @return array|mixed
     *
     * @throws Exception
     */
    public function getCaptchaApi(Captcha $captcha, string $config = 'default')
    {
        return $captcha->create($config, true);
    }
}
