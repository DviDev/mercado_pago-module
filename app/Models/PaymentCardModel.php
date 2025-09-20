<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Models;

use Modules\Base\Contracts\BaseModel;
use Modules\Base\Factories\BaseFactory;
use Modules\MercadoPago\Entities\PaymentCard\PaymentCardEntityModel;
use Modules\MercadoPago\Entities\PaymentCard\PaymentCardProps;

/**
 * @author Davi Menezes (davimenezes.dev@gmail.com)
 *
 * @link https://github.com/DaviMenezes
 *
 * @property-read PaymentCardModel $model
 *
 * @method PaymentCardEntityModel toEntity()
 */
final class PaymentCardModel extends BaseModel
{
    use PaymentCardProps;

    public static function table($alias = null): string
    {
        return self::dbTable('mp_payment_cards', $alias);
    }

    public function modelEntity(): string
    {
        return PaymentCardEntityModel::class;
    }

    protected static function newFactory(): BaseFactory
    {
        return new class extends BaseFactory
        {
            protected $model = PaymentCardModel::class;
        };
    }
}
