<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Listeners;

use Modules\DBMap\Domains\ScanTableDomain;

final class ScanTableMercadoPagoListener
{
    public function handle($event): void
    {
        (new ScanTableDomain)->scan('MercadoPago');
    }
}
