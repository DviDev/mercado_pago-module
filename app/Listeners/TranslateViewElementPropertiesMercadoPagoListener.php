<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Listeners;

use Modules\Base\Contracts\BaseTranslateViewElementPropertiesListener;

final class TranslateViewElementPropertiesMercadoPagoListener extends BaseTranslateViewElementPropertiesListener
{
    protected function moduleName(): string
    {
        return config('mercadopago.name');
    }

    protected function moduleNameLower(): string
    {
        return mb_strtolower(config('mercadopago.name'));
    }
}
