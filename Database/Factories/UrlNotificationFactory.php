<?php

namespace Modules\MercadoPago\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Base\Factories\BaseFactory;
use Modules\MercadoPago\Models\UrlNotificationModel;
use Modules\MercadoPago\Entities\UrlNotification\UrlNotificationEntityModel;

/**
 * @method UrlNotificationModel create(array $attributes = [])
 * @method UrlNotificationModel make(array $attributes = [])
 */
class UrlNotificationFactory extends BaseFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UrlNotificationModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $p = UrlNotificationEntityModel::props(null, true);
        return [

        ];
    }
}
