<?php

namespace Modules\MercadoPago\Tests\Tables;

use Modules\Base\Contracts\BaseModel;
use Modules\Base\Services\Tests\BaseTest;
use Modules\MercadoPago\Models\PaymentModel;

class PaymentTableTest extends BaseTest
{
    public function getModelClass(): string|BaseModel
    {
        return PaymentModel::class;
    }
}
