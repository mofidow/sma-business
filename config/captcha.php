<?php

return [
    'secret'     => env('CAPTCHA_SECRET'),
    'sitekey'    => env('CAPTCHA_SITEKEY'),
    'disable'    => env('CAPTCHA_DISABLE', false),
    'characters' => ['2', '3', '4', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'j', 'm', 'n', 'p', 'q', 'r', 't', 'u', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'M', 'N', 'P', 'Q', 'R', 'T', 'U', 'X', 'Y', 'Z'],
    'default'    => [
        'length'  => 6,
        'width'   => 120,
        'height'  => 36,
        'quality' => 90,
        'math'    => false,
        'expire'  => 60,
        'encrypt' => false,
    ],
    'math' => [
        'length'  => 9,
        'width'   => 120,
        'height'  => 36,
        'quality' => 90,
        'math'    => true,
    ],

    'flat' => [
        'length'     => 6,
        'width'      => 160,
        'height'     => 46,
        'quality'    => 90,
        'lines'      => 6,
        'bgImage'    => false,
        'bgColor'    => '#fff7ed',
        'fontColors' => ['#3f3f46', '#b91c1c', '#b45309', '#15803d', '#0e7490', '#1d4ed8', '#7e22ce', '#9f1239'],
        'contrast'   => -5,
    ],
    'mini' => [
        'length' => 3,
        'width'  => 60,
        'height' => 32,
    ],
    'inverse' => [
        'length'    => 5,
        'width'     => 120,
        'height'    => 36,
        'quality'   => 90,
        'sensitive' => true,
        'angle'     => 12,
        'sharpen'   => 10,
        'blur'      => 2,
        'invert'    => true,
        'contrast'  => -5,
    ],
];
