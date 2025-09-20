<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Listeners;

use Modules\Project\Contracts\CreateMenuItemsListenerContract;

final class CreateMenuItemsMercadoPagoListener extends CreateMenuItemsListenerContract
{
    protected function moduleName(): string
    {
        return config('mercadopago.name');
    }
}
