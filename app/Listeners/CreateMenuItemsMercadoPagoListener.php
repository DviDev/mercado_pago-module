<?php

namespace Modules\MercadoPago\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Project\Contracts\CreateMenuItemsListenerContract;

class CreateMenuItemsMercadoPagoListener extends CreateMenuItemsListenerContract
{
    public function moduleName(): string
    {
        return config('mercadopago.name');
    }
}
