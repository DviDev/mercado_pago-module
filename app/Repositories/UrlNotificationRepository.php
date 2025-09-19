<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Base\Repository\BaseRepository;
use Modules\MercadoPago\Entities\UrlNotification\UrlNotificationEntityModel;
use Modules\MercadoPago\Models\UrlNotificationModel;

/**
 * @author Davi Menezes(davimenezes.dev@gmail.com)
 *
 * @link https://github.com/DaviMenezes
 *
 * @method self obj()
 * @method UrlNotificationModel model()
 * @method UrlNotificationEntityModel find($id)
 * @method UrlNotificationModel first()
 * @method UrlNotificationModel findOrNew($id)
 * @method UrlNotificationModel firstOrNew(Builder|\Illuminate\Database\Query\Builder $query)
 * @method UrlNotificationEntityModel findOrFail($id)
 */
final class UrlNotificationRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    public function modelClass(): string
    {
        return UrlNotificationModel::class;
    }
}
