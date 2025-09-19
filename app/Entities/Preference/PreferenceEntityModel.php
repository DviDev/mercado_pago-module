<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Entities\Preference;

use Modules\Base\Entities\BaseEntityModel;
use Modules\MercadoPago\Models\PreferenceModel;
use Modules\MercadoPago\Repositories\PreferenceRepository;

/**
 * @author Davi Menezes (davimenezes.dev@gmail.com)
 *
 * @link https://github.com/DaviMenezes
 *
 * @property-read PreferenceModel $model
 *
 * @method self save()
 * @method static self new()
 * @method static self props($alias = null, $force = null)
 * @method PreferenceRepository repository()
 */
final class PreferenceEntityModel extends BaseEntityModel
{
    use PreferenceProps;

    protected function repositoryClass(): string
    {
        return PreferenceRepository::class;
    }
}
