<?php

namespace Modules\MercadoPago\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Base\Factories\BaseFactory;
use Modules\Base\Models\BaseModel;
use Modules\MercadoPago\Entities\Preference\PreferenceEntityModel;
use Modules\MercadoPago\Entities\Preference\PreferenceProps;
use Modules\Store\Models\OrderModel;

/**
 * @author Davi Menezes (davimenezes.dev@gmail.com)
 * @link https://github.com/DaviMenezes
 * @property-read OrderModel $order
 * @method PreferenceEntityModel toEntity()
 */
class PreferenceModel extends BaseModel
{
    use HasFactory;
    use PreferenceProps;

    protected $fillable = ['user_id', 'order_id', 'mp_preference_id', 'collector_id', 'client_id'];

    public static function table($alias = null): string
    {
        return self::dbTable('mp_preferences', $alias);
    }

    public static function getByStringId($id): ?PreferenceModel
    {
        return self::whereFn(fn(PreferenceEntityModel $p) => [
            [$p->mp_preference_id, $id]
        ])->first();
    }

    protected static function newFactory(): BaseFactory
    {
        return new class extends BaseFactory {
            protected $model = PreferenceModel::class;
        };
    }

    public function modelEntity(): string
    {
        return PreferenceEntityModel::class;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }
}
