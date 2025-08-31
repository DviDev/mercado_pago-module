<?php

namespace Modules\MercadoPago\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Base\Contracts\BaseModel;
use Modules\Base\Factories\BaseFactory;
use Modules\MercadoPago\Entities\WebhookNotification\WebhookNotificationEntityModel;
use Modules\MercadoPago\Entities\WebhookNotification\WebhookNotificationProps;

;

/**
 * @author Davi Menezes (davimenezes.dev@gmail.com)
 *
 * @link https://github.com/DaviMenezes
 *
 * @property-read PaymentModel $payment
 *
 * @method WebhookNotificationEntityModel toEntity()
 */
class WebhookNotificationModel extends BaseModel
{
    use WebhookNotificationProps;

    protected $casts = ['live_mode' => 'boolean'];
    protected $fillable = [
        'action',
        'api_version',
        'data_id',
        'created_at',
        'mp_id',
        'live_mode',
        'type',
        'user_id',
        'date_created',
    ];

    public static function table($alias = null): string
    {
        return self::dbTable('mp_webhook_notifications', $alias);
    }

    protected static function newFactory(): BaseFactory
    {
        return new class extends BaseFactory {
            protected $model = WebhookNotificationModel::class;
        };
    }

    public function modelEntity(): string
    {
        return WebhookNotificationEntityModel::class;
    }

    public function payment(): HasOne
    {
        return $this->hasOne(PaymentModel::class, 'notification_id');
    }
}
