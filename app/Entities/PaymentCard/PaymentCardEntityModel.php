<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Entities\PaymentCard;

use Modules\Base\Entities\BaseEntityModel;
use Modules\MercadoPago\Models\PaymentCardModel;

/**
 * @author Davi Menezes (davimenezes.dev@gmail.com)
 *
 * @link https://github.com/DaviMenezes
 *
 * @property-read PaymentCardModel $model
 *
 * @method self save()
 * @method static self new()
 * @method static self props($alias = null, $force = null)
 */
final class PaymentCardEntityModel extends BaseEntityModel
{
    use PaymentCardProps;
}
