<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Entities\UrlNotification;

use Modules\Base\Entities\BaseEntityModel;
use Modules\MercadoPago\Models\UrlNotificationModel;
use Modules\MercadoPago\Repositories\UrlNotificationRepository;

/**
 * @author Davi Menezes (davimenezes.dev@gmail.com)
 *
 * @link https://github.com/DaviMenezes
 *
 * @property-read UrlNotificationModel $model
 *
 * @method self save()
 * @method static self new()
 * @method static self props($alias = null, $force = null)
 * @method UrlNotificationRepository repository()
 */
final class UrlNotificationEntityModel extends BaseEntityModel
{
    use UrlNotificationProps;

    protected function repositoryClass(): string
    {
        return UrlNotificationRepository::class;
    }
}
