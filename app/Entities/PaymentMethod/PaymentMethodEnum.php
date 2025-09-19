<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Entities\PaymentMethod;

enum PaymentMethodEnum: string
{
    case BOLBRADESCO = 'bolbradesco';
    case PIX = 'pix';
    case TICKET = 'ticket';
}
