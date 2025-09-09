<?php

namespace Modules\MercadoPago\Listeners;

use Modules\DBMap\Domains\ScanTableDomain;

class ScanTableMercadoPagoListener
{
    public function handle($event): void
    {
        new ScanTableDomain()->scan('MercadoPago');
    }
}
