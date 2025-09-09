<?php

namespace Modules\MercadoPago\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Base\Contracts\BaseTranslateViewElementPropertiesListener;

class TranslateViewElementPropertiesMercadoPagoListener extends BaseTranslateViewElementPropertiesListener
{
    protected function moduleName(): string
    {
        return config('mercadopago.name');
    }

    protected function moduleNameLower(): string
    {
        return strtolower(config('mercadopago.name'));
    }
}
