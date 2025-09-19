<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Tests\Tables;

use Modules\Base\Contracts\BaseModel;
use Modules\Base\Services\Tests\BaseTest;
use Modules\MercadoPago\Models\PreferenceModel;

final class PreferenceTableTest extends BaseTest
{
    public function getModelClass(): string|BaseModel
    {
        return PreferenceModel::class;
    }
}
