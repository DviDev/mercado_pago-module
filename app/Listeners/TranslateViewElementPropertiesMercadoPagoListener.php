<?php

namespace Modules\MercadoPago\Listeners;

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
