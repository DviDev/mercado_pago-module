<?php

namespace Modules\MercadoPago\Entities\UrlNotification;

use Modules\Base\Entities\BaseEntityModel;
use Modules\MercadoPago\Repositories\UrlNotificationRepository;
use Modules\MercadoPago\Models\UrlNotificationModel;

/**
 * @author Davi Menezes (davimenezes.dev@gmail.com)
 * @link https://github.com/DaviMenezes
 * @property-read UrlNotificationModel $model
 * @method self save()
 * @method static self new()
 * @method static self props($alias = null, $force = null)
 * @method UrlNotificationRepository repository()
 */
class UrlNotificationEntityModel extends BaseEntityModel
{
    use UrlNotificationProps;

    protected function repositoryClass(): string
    {
        return UrlNotificationRepository::class;
    }
}
