<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Tests\Tables;

use Modules\Base\Contracts\BaseModel;
use Modules\Base\Services\Tests\BaseTest;
use Modules\MercadoPago\Models\PaymentCardModel;

final class PaymentCardTableTest extends BaseTest
{
    public function getModelClass(): string|BaseModel
    {
        return PaymentCardModel::class;
    }
}
