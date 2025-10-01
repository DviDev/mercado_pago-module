<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Tests\Tables;

use Modules\Base\Contracts\BaseModel;
use Modules\Base\Contracts\BaseTest;
use Modules\MercadoPago\Models\PaymentModel;

final class PaymentTableTest extends BaseTest
{
    public function getModelClass(): string|BaseModel
    {
        return PaymentModel::class;
    }
}
