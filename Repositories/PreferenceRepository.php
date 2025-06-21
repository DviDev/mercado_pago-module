<?php

namespace Modules\MercadoPago\Repositories;

use Modules\Base\Repository\BaseRepository;
use Modules\MercadoPago\Entities\Preference\PreferenceEntityModel;
use Modules\MercadoPago\Models\PreferenceModel;

/**
 * @author Davi Menezes(davimenezes.dev@gmail.com)
 *
 * @link https://github.com/DaviMenezes
 *
 * @method self obj()
 * @method PreferenceModel model()
 * @method PreferenceEntityModel find($id)
 * @method PreferenceModel first()
 * @method PreferenceModel findOrNew($id)
 * @method PreferenceModel firstOrNew($query)
 * @method PreferenceEntityModel findOrFail($id)
 */
class PreferenceRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    public function modelClass(): string
    {
        return PreferenceModel::class;
    }
}
