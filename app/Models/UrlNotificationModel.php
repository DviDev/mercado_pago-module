<?php

namespace Modules\MercadoPago\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Base\Contracts\BaseModel;
use Modules\Base\Factories\BaseFactory;
use Modules\MercadoPago\Entities\UrlNotification\UrlNotificationEntityModel;
use Modules\MercadoPago\Entities\UrlNotification\UrlNotificationProps;

;

/**
 * @author Davi Menezes (davimenezes.dev@gmail.com)
 *
 * @link https://github.com/DaviMenezes
 *
 * @method UrlNotificationEntityModel toEntity()
 */
class UrlNotificationModel extends BaseModel
{
    use HasFactory;
    use UrlNotificationProps;

    protected $fillable = [
        'collection_id',
        'collection_status',
        'payment_id',
        'status',
        'external_reference',
        'payment_type',
        'merchant_order_id',
        'preference_id',
        'site_id',
        'processing_mode',
        'merchant_account_id',
    ];

    public static function table($alias = null): string
    {
        return self::dbTable('mp_back_url_notifications', $alias);
    }

    protected static function newFactory(): BaseFactory
    {
        return new class extends BaseFactory
        {
            protected $model = UrlNotificationModel::class;
        };
    }

    public function modelEntity(): string
    {
        return UrlNotificationEntityModel::class;
    }
}
