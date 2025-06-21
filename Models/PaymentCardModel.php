<?php

namespace Modules\MercadoPago\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Base\Factories\BaseFactory;
use Modules\Base\Models\BaseModel;
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
class PaymentCardModel extends BaseModel
{
    use HasFactory;
    use PaymentCardProps;

    public static function table($alias = null): string
    {
        return self::dbTable('mp_payment_cards', $alias);
    }

    protected static function newFactory(): BaseFactory
    {
        return new class extends BaseFactory
        {
            protected $model = PaymentCardModel::class;
        };
    }

    public function modelEntity(): string
    {
        return PaymentCardEntityModel::class;
    }
}
