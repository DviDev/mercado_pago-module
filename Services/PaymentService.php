<?php

namespace Modules\MercadoPago\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\Payment;
use Modules\MercadoPago\Models\PaymentModel;

class PaymentService
{
    public static function createBoleto(
        $order_id,
        $amount,
        $idempotency,
        User $userCustomer,
        $description): Payment
    {
        MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));

        $client = new PaymentClient;
        $request_options = new RequestOptions;
        $request_options->setCustomHeaders(['X-Idempotency-Key: '.$idempotency]);

        //        $customer = $this->proposta->customer;
        $name_array = str($userCustomer->name)->explode(' ');

        $address = $userCustomer->person->firstAddress();
        $type = $userCustomer->person->human ? 'CPF' : 'CNPJ';
        $number = $userCustomer->person->human?->cpf ?: $userCustomer->person->firm->cnpj;
        $request = [
            'transaction_amount' => $amount,
            'token' => config('mercadopago.access_token'),
            'description' => $description,
            'installments' => 1,
            'payment_method_id' => 'bolbradesco',
            'payer' => [
                'email' => $userCustomer->email,
                'first_name' => $name_array->shift(),
                'last_name' => $name_array->join(' '),
                'identification' => [
                    'type' => $type,
                    'number' => $number,
                ],
                'address' => [
                    'zip_code' => $address->zip_code,
                    'street_name' => $address->street_name,
                    'street_number' => $address->number,
                    'neighborhood' => $address->neighborhood,
                    'city' => $address->city,
                    'federal_unit' => $address->state,
                ],
            ],
            'external_reference' => "order-$order_id",
        ];
        try {
            return $client->create($request, $request_options);
        } catch (MPApiException $exception) {
            $content = $exception->getApiResponse()->getContent();
            throw new \Exception(json_encode($content));
        }
    }

    public static function gerarPix(
        $order_id,
        $amount,
        $idempotency_key,
        $customer_name, $customer_email, $document_type, $document, $description): Payment
    {
        $payment = self::generatePix($idempotency_key, $customer_name, $amount, $description, $customer_email, $document_type, $document, $order_id);

        PaymentModel::criaViaPaymentMercadoPago($payment, $order_id);

        return $payment;
    }

    protected static function generatePix(
        $idempotency_key,
        $customer_name,
        $amount,
        $description,
        $customer_email,
        $document_type,
        $document,
        $order_id
    ): Payment {
        try {
            MercadoPagoConfig::setAccessToken(config('mercadopago.access_token'));

            $client = new PaymentClient;
            $request_options = new RequestOptions;
            $request_options->setCustomHeaders(['X-Idempotency-Key: '.$idempotency_key]);

            $name_array = str($customer_name)->explode(' ');
            $first_name = $name_array->shift();
            $last_name = $name_array->join(' ');

            $payment = $client->create([
                'transaction_amount' => $amount,
                'token' => config('mercadopago.access_token'),
                'description' => $description,
                'installments' => 1,
                'payment_method_id' => 'pix',
                'issuer_id' => 2006,
                'payer' => [
                    'email' => $customer_email,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'identification' => [
                        'type' => $document_type,
                        'number' => $document,
                    ],
                ],
                'external_reference' => "order-$order_id",
            ], $request_options);

            return $payment;
        } catch (MPApiException $exception) {
            Log::info('==================================================');
            Log::error($exception->getMessage());
            Log::info('==================================================');
            Log::error($exception->getApiResponse()->getContent());
            Log::info('==================================================');
            Log::error($exception->getTraceAsString());
            Log::info('==================================================');
            Log::error($exception->getTraceAsString());
            throw $exception;
        }
    }
}
