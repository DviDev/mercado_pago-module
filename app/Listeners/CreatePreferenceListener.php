<?php

namespace Modules\MercadoPago\Listeners;

use Modules\MercadoPago\Entities\PreprefenceItem\PreferenceItemDTO;
use Modules\MercadoPago\Models\PreferenceModel;
use Modules\Store\Events\OrderWithItemsCreatedEvent;

class CreatePreferenceListener
{

    public function handle(OrderWithItemsCreatedEvent $event): void
    {
        $items = [];
        foreach ($event->order->items as $item) {
            $items[] = new PreferenceItemDTO(
                id: $event->order->id.'#'.$item->id,
                title: $item->description,
                unit_price: $item->price * $item->quantity
            );
        }
        PreferenceModel::createMpPreference($event->order, $items);
    }
}
