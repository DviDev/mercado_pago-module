<?php

namespace Modules\MercadoPago\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\Payment;
use Modules\Base\Factories\BaseFactory;
use Modules\Base\Models\BaseModel;
use Modules\MercadoPago\Entities\Payment\PaymentEntityModel;
use Modules\MercadoPago\Entities\Payment\PaymentProps;
use Modules\Seguros\Models\PropostaModel;
use Modules\Store\Models\OrderModel;

/**
 * @author Davi Menezes (davimenezes.dev@gmail.com)
 * @link https://github.com/DaviMenezes
 * @property-read OrderModel $order
 * @method PaymentEntityModel toEntity()
 */
class PaymentModel extends BaseModel
{
    use HasFactory;
    use PaymentProps;

    protected $casts = ['created_at' => 'datetime'];

    public static function table($alias = null): string
    {
        return self::dbTable('mp_payments', $alias);
    }

    public static function getByMpId(string $id): ?PaymentModel
    {
        $p = PaymentEntityModel::props();
        return self::where($p->mp_id,$id)->get()->first();
    }

    public static function makeViaApiData(mixed $data): PaymentModel
    {
        $p = PaymentEntityModel::props(null, true);
        $payment = new PaymentModel();
        $payment->mp_id = $data[$p->id];
        $payment->external_reference = $data[$p->external_reference];
        $payment->collector_id = $data[$p->collector_id];
        $payment->date_approved = $data[$p->date_approved];
        $payment->date_created = $data[$p->date_created];
        $payment->description = $data[$p->description];
        $payment->installments = $data[$p->installments];
        $payment->operation_type = $data[$p->operation_type];
        $payment->payment_method_id = $data[$p->payment_method_id];
        $payment->transaction_details_installment_amount = $data['transaction_details']['installment_amount'];
        $payment->transaction_details_total_paid_amount = $data['transaction_details']['total_paid_amount'];
        $payment->payment_type_id = $data[$p->payment_type_id];
        $payment->status = $data[$p->status];
        $payment->status_detail = $data[$p->status_detail];
        $payment->transaction_amount = $data[$p->transaction_amount];
        return $payment;
    }

    public static function createViaPaymentMercadoPago(Payment $payment, $order_id): ?PaymentModel
    {
        if (PaymentModel::query()->where(['mp_id' => $payment->id])->exists()) {
            return null;
        }

        return PaymentModel::createFn(fn(PaymentEntityModel $p) => [
            $p->mp_id => $payment->id,
            $p->collector_id => $payment->collector_id,
            $p->date_approved => $payment->date_approved,
            $p->date_created => $payment->date_created,
            $p->description => $payment->description,
            $p->installments => $payment->installments,
            $p->operation_type => $payment->operation_type,
            $p->status => $payment->status,
            $p->status_detail => $payment->status_detail,
            $p->transaction_amount => $payment->transaction_amount,
            $p->transaction_details_installment_amount => $payment->transaction_details->installment_amount,
            $p->transaction_details_net_received_amount => $payment->transaction_details->net_received_amount,
            $p->transaction_details_total_paid_amount => $payment->transaction_details->total_paid_amount,
            $p->transaction_details_external_resource_url => $payment->transaction_details?->external_resource_url,
            $p->transaction_details_barcode_content => $payment->transaction_details->barcode['content'] ?? null,
            $p->payment_method_id => $payment->payment_method_id,
            $p->payment_type_id => $payment->payment_type_id,
            $p->point_of_interaction_type => $payment->point_of_interaction->type,
            $p->point_of_interaction_transaction_qr_code => $payment->point_of_interaction->transaction_data->qr_code ?? null,
            $p->point_of_interaction_transaction_ticket_url => $payment->point_of_interaction->transaction_data->ticket_url ?? null,
            $p->notification_id => null,
            $p->order_id => $order_id,
            $p->external_reference => $payment->external_reference,
            $p->transaction_details_digitable_line => $payment->transaction_details->digitable_line ?? null,
        ]);
    }

    protected static function newFactory(): BaseFactory
    {
        return new class extends BaseFactory {
            protected $model = PaymentModel::class;
        };
    }

    public function modelEntity(): string
    {
        return PaymentEntityModel::class;
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id');
    }

    public function getDescription(): ?string
    {
        return match ($this->status_detail) {
            'accredited' => 'O pagamento foi aprovado',
            'cc_rejected_other_reason' => "Seu cartão recusou o pagamento.<br> Use outro cartão ou outro meio de pagamento",
            'cc_rejected_call_for_authorize' => 'O cartão foi rejeitado. Ligue para autorizar',
            'cc_rejected_insufficient_amount' => 'O seu cartão não tem limite suficiente',
            'cc_rejected_bad_filled_security_code' => 'O código de segurança do cartão é inválido',
            'cc_rejected_bad_filled_date' => 'O vencimento do cartão é inválido',
            'cc_rejected_bad_filled_other' => 'Algum dado do cartão é inválido',
            'cc_rejected_high_risk' => 'O cartão não foi aceito',
            'pending_waiting_transfer' => 'Aguardando transferência',
            'expired' => 'A transação expirou',
            default => $this->status_detail
        };
    }

    public function gerarPix(
                             $order_id,
                             $amount,
                             $idempotency_key,
                             $customer_name, $customer_email, $customer_cpf, $description)
    {
        MercadoPagoConfig::setAccessToken(env('MERCADO_PAGO_ACCESS_TOKEN_PROD'));

        $client = new PaymentClient();
        $request_options = new RequestOptions();
        $request_options->setCustomHeaders(["X-Idempotency-Key: ".$idempotency_key]);

        $name_array = str($customer_name)->explode(' ');
        $first_name = $name_array->shift();
        $last_name = $name_array->join(' ');

        $payment = $client->create([
            "transaction_amount" => $amount,
            "token" => env('MERCADO_PAGO_ACCESS_TOKEN_PROD'),
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

    public function getMpPayment(): Payment
    {
        MercadoPagoConfig::setAccessToken(env('MERCADO_PAGO_ACCESS_TOKEN_PROD'));
        $client = new PaymentClient();
//        $request_options = new RequestOptions();
//        $request_options->setCustomHeaders(["X-Idempotency-Key: ".$idempotency_key]);
        return $client->get($this->mp_id);
    }
}
