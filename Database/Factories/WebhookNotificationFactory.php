<?php

namespace Modules\MercadoPago\Database\Factories;

use Modules\Base\Factories\BaseFactory;
use Modules\MercadoPago\Models\WebhookNotificationModel;

/**
 * @method WebhookNotificationModel create(array $attributes = [])
 * @method WebhookNotificationModel make(array $attributes = [])
 */
class WebhookNotificationFactory extends BaseFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WebhookNotificationModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return $this->getValues();
    }
}
