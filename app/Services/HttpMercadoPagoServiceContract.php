<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Services;

use Modules\Base\Services\HttpContract;

abstract class HttpMercadoPagoServiceContract extends HttpContract
{
    protected function errorType(): int
    {
        return 500;
    }

    protected function url()
    {
        return config('mercado-pago.URL');
    }

    protected function loginContract()
    {
        return null;
    }

    protected function moduleName(): string
    {
        return 'MercadoPago';
    }
}
