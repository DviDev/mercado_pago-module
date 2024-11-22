<?php

namespace Modules\MercadoPago\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use Modules\Base\Factories\BaseFactory;
use Modules\Base\Models\BaseModel;
use Modules\MercadoPago\Entities\PaymentMethod\PaymentMethodEnum;
use Modules\MercadoPago\Entities\Preference\PreferenceEntityModel;
use Modules\MercadoPago\Entities\Preference\PreferenceProps;
use Modules\MercadoPago\Entities\PreprefenceItem\PreferenceItemDTO;
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

    /**
     * @param PreferenceItemDTO[] $items
     * @param PaymentMethodEnum[] $excluded_payment_methods
     * @param PaymentMethodEnum[] $excluded_payment_types
     * @throws MPApiException
     */
    static public function createMpPreference(
        OrderModel $order,
        array $items,
        array      $excluded_payment_methods = [],
        array      $excluded_payment_types = []
    ): ?PreferenceModel
    {
        if (!config('mercadopago.enable')) {
            return null;
        }

        foreach ($items as $item) {
            if (!$item instanceof PreferenceItemDTO) {
                throw new \InvalidArgumentException(__('mercadopago::preference.All items must be instances of ', ['class' => PreferenceItemDTO::class]));
            }
        }
        try {
            \DB::beginTransaction();

            $preference = new PreferenceModel();
            $preference->user_id = auth()->user()->id;
            $preference->order_id = $order->id;
            $preference->save();

            MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));
            $client = new PreferenceClient();

            $mp_items = collect($items)->map(fn($item) => $item->toArray());
            $mp_excluded_payment_methods = collect($excluded_payment_methods)->map(fn(PaymentMethodEnum $item) => ['id' => $item->value]);
            $mp_excluded_payment_types = collect($excluded_payment_types)->map(fn(PaymentMethodEnum $item) => ['id' => $item->value]);

            $client_array = ["external_reference" => $preference->id];

            if ($mp_items->count() > 0) {
                $client_array["items"] = $mp_items->toArray();
            }
            if ($mp_excluded_payment_types->count() > 0) {
                $client_array['payment_methods']['excluded_payment_types'] = $mp_excluded_payment_types->toArray();
            }
            if ($mp_excluded_payment_methods->count() > 0) {
                $client_array['payment_methods']['excluded_payment_methods'] = $mp_excluded_payment_methods->toArray();
            }

            //Todo back_urls testing... wip
            /*$client_array['back_urls'] = [
                "success" => route('order.status.success', $order->id),
                "failure" => route('order.status.failure', $order->id),
                "pending" => route('order.status.pending', $order->id)
            ];*/

            $mp_preference = $client->create($client_array);

            $preference->mp_preference_id = $mp_preference->id;
            $preference->collector_id = $mp_preference->collector_id;
            $preference->client_id = $mp_preference->client_id;
            $preference->save();

            \DB::commit();

            return $preference;
        } catch (MPApiException $exception) {
            throw new \Exception(
                message: collect($exception->getApiResponse()->getContent())->join(PHP_EOL),
                code: $exception->getApiResponse()->getStatusCode(),
                previous: $exception);
        } catch (\Exception $exception) {
            \DB::rollBack();
            throw $exception;
        }
    }

}
