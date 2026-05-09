<?php

$providers = [
    App\Providers\AppServiceProvider::class,
    App\Providers\FortifyServiceProvider::class,
    App\Providers\JetstreamServiceProvider::class,
    Mews\Captcha\CaptchaServiceProvider::class,
];

if (file_exists(base_path('modules/Shop/ShopServiceProvider.php'))) {
    $providers[] = Modules\Shop\ShopServiceProvider::class;
}

return $providers;
