<?php

namespace Modules\MercadoPago\Database\Factories;

use Modules\Base\Factories\BaseFactory;
use Modules\MercadoPago\Models\PaymentModel;

/**
 * @method PaymentModel create(array $attributes = [])
 * @method PaymentModel make(array $attributes = [])
 */
class PaymentFactory extends BaseFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PaymentModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        $values = $this->getValues();
        return $values;
    }
}
