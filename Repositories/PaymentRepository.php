<?php

namespace Modules\MercadoPago\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Base\Repository\BaseRepository;
use Modules\MercadoPago\Models\PaymentModel;

/**
 * @author Davi Menezes(davimenezes.dev@gmail.com)
 *
 * @link https://github.com/DaviMenezes
 *
 * @method self obj()
 * @method PaymentModel model()
 * @method PaymentModel find($id)
 * @method PaymentModel first()
 * @method PaymentModel findOrNew($id)
 * @method PaymentModel firstOrNew(Builder|\Illuminate\Database\Query\Builder $query)
 * @method PaymentModel findOrFail($id)
 */
class PaymentRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    public function modelClass(): string
    {
        return PaymentModel::class;
    }
}
