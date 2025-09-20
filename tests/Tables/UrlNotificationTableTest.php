<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Tests\Tables;

use Modules\Base\Contracts\BaseModel;
use Modules\Base\Services\Tests\BaseTest;
use Modules\MercadoPago\Models\UrlNotificationModel;

final class UrlNotificationTableTest extends BaseTest
{
    public function getModelClass(): string|BaseModel
    {
        return UrlNotificationModel::class;
    }
}
