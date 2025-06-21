<?php

namespace Modules\MercadoPago\Listeners;

use Modules\MercadoPago\Entities\PreprefenceItem\PreferenceItemDTO;
use Modules\MercadoPago\Models\PreferenceModel;
use Modules\Store\App\Events\OrderWithItemsCreatedEvent;

class CreatePreferenceListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
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
