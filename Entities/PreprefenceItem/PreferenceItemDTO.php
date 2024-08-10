<?php

namespace Modules\MercadoPago\Entities\PreprefenceItem;

use Illuminate\Contracts\Support\Arrayable;

class PreferenceItemDTO implements Arrayable
{
    public function __construct(
        protected string|int $id,
        protected string     $title,
        protected float      $unit_price,
        protected int        $quantity = 1,
        protected string     $currency_id = "BRL",
    )
    {
    }

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
