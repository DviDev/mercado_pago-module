<?php

return [
    'name' => 'MercadoPago',
    'key' => config('app.env') == 'local'
        ? env('MERCADO_PAGO_KEY')
        : env('MERCADO_PAGO_KEY_PROD'),
    'access_token' => config('app.env') == 'local'
        ? env('MERCADO_PAGO_ACCESS_TOKEN')
        : env('MERCADO_PAGO_ACCESS_TOKEN_PROD'),
    'webhook_key' => env('MERCADO_PAGO_WEBHOOK_KEY'),
    'payment_methods' => [
        'pix' => env('app_env') == 'local' ? false : env('MERCADO_PAGO_PAYMENT_METHOD_PIX', false)
    ],
    'debug' => [
        'webhook' => [
            'payment' => env('MODULE_SEGURO_DEBUG_WEBHOOK_PAYMENT', false),
            'notification' => env('MODULE_SEGURO_DEBUG_WEBHOOK_NOTIFICATION', false),
        ]
    ]
];
