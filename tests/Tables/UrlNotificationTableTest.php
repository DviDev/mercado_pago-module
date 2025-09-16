<?php

namespace Modules\MercadoPago\Tests\Tables;

use Modules\Base\Contracts\BaseModel;
use Modules\Base\Services\Tests\BaseTest;
use Modules\MercadoPago\Models\UrlNotificationModel;

class UrlNotificationTableTest extends BaseTest
{
    public function getModelClass(): string|BaseModel
    {
        return UrlNotificationModel::class;
    }
}
