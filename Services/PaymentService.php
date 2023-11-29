<?php

namespace Modules\MercadoPago\Services;

use App\Models\User;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\Payment;

class PaymentService
{
    public static function createBoleto($amount, $idempotency, User $customer, $description): Payment
    {
        MercadoPagoConfig::setAccessToken(env('MERCADO_PAGO_ACCESS_TOKEN_PROD'));

        $client = new PaymentClient();
        $request_options = new RequestOptions();
        $request_options->setCustomHeaders(["X-Idempotency-Key: " .$idempotency]);

//        $customer = $this->proposta->customer;
        $name_array = str($customer->name)->explode(' ');

        $address = $customer->person->address();
        return $client->create([
            "transaction_amount" => $amount,
            "token" => env('MERCADO_PAGO_ACCESS_TOKEN_PROD'),
            "description" => $description,
            "installments" => 1,
            "payment_method_id" => 'bolbradesco',
            "issuer_id" => 2006,
            "payer" => [
                "email" => $customer->email,
                "first_name" => $name_array->shift(),
                "last_name" => $name_array->join(' '),
                "identification" => [
                    "type" => 'CPF',
                    "number" => $customer->person->human->cpf
                ],
                "address" => array(
                    "zip_code" => $address->zip_code,
                    "street_name" => $address->street_name,
                    "street_number" => $address->number,
                    "neighborhood" => $address->neighborhood,
                    "city" => $address->city,
                    "federal_unit" => $address->state
                )
            ]
        ], $request_options);
    }
}
