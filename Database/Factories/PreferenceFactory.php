<?php

namespace Modules\MercadoPago\Database\Factories;

use Modules\Base\Factories\BaseFactory;
use Modules\MercadoPago\Models\PreferenceModel;

/**
 * @method PreferenceModel create(array $attributes = [])
 * @method PreferenceModel make(array $attributes = [])
 */
class PreferenceFactory extends BaseFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PreferenceModel::class;

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
