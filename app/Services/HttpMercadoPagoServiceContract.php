<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Services;

use Modules\Base\Contracts\HttpContract;

abstract class HttpMercadoPagoServiceContract extends HttpContract
{
    protected function errorType(): int
    {
        return 500;
    }

    protected function url(): string
    {
        return config('mercado-pago.URL');
    }

    protected function loginContract(): mixed
    {
        return null;
    }

    protected function moduleName(): string
    {
        return 'MercadoPago';
    }
}
