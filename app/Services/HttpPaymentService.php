<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Services;

use Illuminate\Support\Facades\Http;

final class HttpPaymentService
{
    public function __construct(public string $access_token) {}

    public function run($payment_id)
    {
        return Http::withHeaders($this->getHeaders())
            ->get('https://api.mercadopago.com/v1/payments/'.$payment_id);
    }

    private function getHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->access_token,
        ];
    }
}
