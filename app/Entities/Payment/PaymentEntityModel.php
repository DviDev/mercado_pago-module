<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Entities\Payment;

use Modules\Base\Entities\BaseEntityModel;
use Modules\MercadoPago\Models\PaymentModel;
use Modules\MercadoPago\Repositories\PaymentRepository;

/**
 * @author Davi Menezes (davimenezes.dev@gmail.com)
 *
 * @link https://github.com/DaviMenezes
 *
 * @property-read PaymentModel $model
 *
 * @method self save()
 * @method static self new()
 * @method static self props($alias = null, $force = null)
 * @method PaymentRepository repository()
 */
final class PaymentEntityModel extends BaseEntityModel
{
    use PaymentProps;

    protected function repositoryClass(): string
    {
        return PaymentRepository::class;
    }
}
