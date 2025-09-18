<?php

namespace Modules\MercadoPago\Listeners;

use Modules\Project\Contracts\DefineSearchableAttributesContract;

class DefineSearchableMercadoPagoAttributes extends DefineSearchableAttributesContract
{
    protected function moduleName(): string
    {
        return config('mercadopago.name');
    }

    protected function searchableFields(): array
    {
        return [];
    }
}
