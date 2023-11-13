<?php

namespace Modules\MercadoPago\Entities\WebhookNotification;

use Modules\Base\Entities\BaseEntityModel;
use Modules\MercadoPago\Models\WebhookNotificationModel;
use Modules\MercadoPago\Repositories\WebhookNotificationRepository;

/**
 * @author Davi Menezes (davimenezes.dev@gmail.com)
 * @link https://github.com/DaviMenezes
 * @property-read WebhookNotificationModel $model
 * @method self save()
 * @method static self new()
 * @method static self props($alias = null, $force = null)
 * @method WebhookNotificationRepository repository()
 */
class WebhookNotificationEntityModel extends BaseEntityModel
{
    use WebhookNotificationProps;

    protected function repositoryClass(): string
    {
        return WebhookNotificationRepository::class;
    }
}
