<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Listeners;

use Modules\Project\Contracts\DefineSearchableAttributesContract;

final class DefineSearchableMercadoPagoAttributes extends DefineSearchableAttributesContract
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
