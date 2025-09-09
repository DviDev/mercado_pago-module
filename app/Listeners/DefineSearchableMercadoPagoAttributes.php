<?php

namespace Modules\MercadoPago\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Project\Contracts\DefineSearchableAttributesContract;

class DefineSearchableMercadoPagoAttributes extends DefineSearchableAttributesContract
{
    protected function moduleName(): string
    {
        return config('mercadopago.name');
    }

    public function searchableFields(): array
    {
        return [];
    }
}
