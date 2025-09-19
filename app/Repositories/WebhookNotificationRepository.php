<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Modules\Base\Repository\BaseRepository;
use Modules\MercadoPago\Entities\WebhookNotification\WebhookNotificationEntityModel;
use Modules\MercadoPago\Models\WebhookNotificationModel;

/**
 * @author Davi Menezes(davimenezes.dev@gmail.com)
 *
 * @link https://github.com/DaviMenezes
 *
 * @method self obj()
 * @method WebhookNotificationModel model()
 * @method WebhookNotificationEntityModel find($id)
 * @method WebhookNotificationModel first()
 * @method WebhookNotificationModel findOrNew($id)
 * @method WebhookNotificationModel firstOrNew(Builder|\Illuminate\Database\Query\Builder $query)
 * @method WebhookNotificationEntityModel findOrFail($id)
 */
final class WebhookNotificationRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    public function modelClass(): string
    {
        return WebhookNotificationModel::class;
    }
}
