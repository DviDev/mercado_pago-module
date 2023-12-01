<?php

namespace Modules\MercadoPago\Services;

use App\Models\User;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\Payment;
use Modules\MercadoPago\Models\PaymentModel;

class PaymentService
{
    public static function createBoleto($amount, $idempotency, User $customer, $description): Payment
    {
        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));

        $client = new PaymentClient();
        $request_options = new RequestOptions();
        $request_options->setCustomHeaders(["X-Idempotency-Key: " .$idempotency]);

//        $customer = $this->proposta->customer;
        $name_array = str($customer->name)->explode(' ');

        $address = $customer->person->address();
        return $client->create([
            "transaction_amount" => $amount,
            "token" => config('mercadopago.access_token'),
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

    public static function gerarPix(
        $order_id,
        $amount,
        $idempotency_key,
        $customer_name, $customer_email, $customer_cpf, $description): Payment
    {
        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));

        $client = new PaymentClient();
        $request_options = new RequestOptions();
        $request_options->setCustomHeaders(["X-Idempotency-Key: ".$idempotency_key]);

        $name_array = str($customer_name)->explode(' ');
        $first_name = $name_array->shift();
        $last_name = $name_array->join(' ');

        $payment = $client->create([
            "transaction_amount" => $amount,
            "token" => config('mercadopago.access_token'),
            "description" => $description,
            "installments" => 1,
            "payment_method_id" => 'pix',
            "issuer_id" => 2006,
            "payer" => [
                "email" => $customer_email,
                "first_name" => $first_name,
                "last_name" => $last_name,
                "identification" => [
                    "type" => 'CPF',
                    "number" => $customer_cpf
                ]
            ]
        ], $request_options);

        PaymentModel::createViaPaymentMercadoPago($payment, $order_id);

        return $payment;
    }
}
