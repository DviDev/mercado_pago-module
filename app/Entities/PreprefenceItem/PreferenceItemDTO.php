<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Entities\PreprefenceItem;

use Illuminate\Contracts\Support\Arrayable;

final class PreferenceItemDTO implements Arrayable
{
    public function __construct(
        private string|int $id,
        private string $title,
        private float $unit_price,
        private int $quantity = 1,
        private string $currency_id = 'BRL',
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'unit_price' => $this->unit_price,
            'quantity' => $this->quantity,
            'currency_id' => $this->currency_id,
        ];
    }
}
