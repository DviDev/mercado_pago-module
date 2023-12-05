<?php

return [
    'name' => 'MercadoPago',
    'key' => config('app.env') == 'local'
        ? env('MERCADO_PAGO_KEY')
        : env('MERCADO_PAGO_KEY_PROD'),
    'access_token' => config('app.env') == 'local'
        ? env('MERCADO_PAGO_ACCESS_TOKEN')
        : env('MERCADO_PAGO_ACCESS_TOKEN_PROD'),
];
