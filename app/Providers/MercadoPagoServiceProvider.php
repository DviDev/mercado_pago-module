<?php

namespace Modules\MercadoPago\Providers;

use Modules\Base\Contracts\BaseServiceProviderContract;
use Modules\DBMap\Events\ScanTableEvent;
use Modules\MercadoPago\Listeners\CreateMenuItemsMercadoPagoListener;
use Modules\MercadoPago\Listeners\CreatePreferenceListener;
use Modules\MercadoPago\Listeners\DefineSearchableMercadoPagoAttributes;
use Modules\MercadoPago\Listeners\ScanTableMercadoPagoListener;
use Modules\MercadoPago\Listeners\TranslateViewElementPropertiesMercadoPagoListener;
use Modules\Project\Events\CreateMenuItemsEvent;
use Modules\Store\Events\OrderWithItemsCreatedEvent;
use Modules\View\Events\DefineSearchableAttributesEvent;
use Modules\View\Events\ElementPropertyCreatingEvent;

class MercadoPagoServiceProvider extends BaseServiceProviderContract
{
    public function provides(): array
    {
        return [
            RouteServiceProvider::class,
        ];
    }

    public function getModuleName(): string
    {
        return 'MercadoPago';
    }

    public function getModuleNameLower(): string
    {
        return 'mercadopago';
    }

    protected function registerEvents(): void
    {
        \Event::listen(OrderWithItemsCreatedEvent::class, CreatePreferenceListener::class);
        \Event::listen(CreateMenuItemsEvent::class, CreateMenuItemsMercadoPagoListener::class);
        \Event::listen(DefineSearchableAttributesEvent::class, DefineSearchableMercadoPagoAttributes::class);
        \Event::listen(ScanTableEvent::class, ScanTableMercadoPagoListener::class);
        \Event::listen(ElementPropertyCreatingEvent::class, TranslateViewElementPropertiesMercadoPagoListener::class);
    }
}
