<?php

namespace Modules\MercadoPago\Entities\Preference;

use Modules\Base\Entities\BaseEntityModel;
use Modules\MercadoPago\Repositories\PreferenceRepository;
use Modules\MercadoPago\Models\PreferenceModel;

/**
 * @author Davi Menezes (davimenezes.dev@gmail.com)
 * @link https://github.com/DaviMenezes
 * @property-read PreferenceModel $model
 * @method self save()
 * @method static self new()
 * @method static self props($alias = null, $force = null)
 * @method PreferenceRepository repository()
 */
class PreferenceEntityModel extends BaseEntityModel
{
    use PreferenceProps;

    protected function repositoryClass(): string
    {
        return PreferenceRepository::class;
    }
}
