<?php

namespace Modules\MercadoPago\Listeners;

use Modules\Project\Contracts\CreateMenuItemsListenerContract;

class CreateMenuItemsMercadoPagoListener extends CreateMenuItemsListenerContract
{
    public function moduleName(): string
    {
        return config('mercadopago.name');
    }
}
