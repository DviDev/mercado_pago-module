<?php

namespace Modules\MercadoPago\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Base\Factories\BaseFactory;
use Modules\Base\Models\BaseModel;
use Modules\MercadoPago\Database\Factories\PaymentFactory;
use Modules\MercadoPago\Entities\Payment\PaymentEntityModel;
use Modules\MercadoPago\Entities\Payment\PaymentProps;
use Modules\Store\Models\OrderModel;

/**
 * @author Davi Menezes (davimenezes.dev@gmail.com)
 * @link https://github.com/DaviMenezes
 * @property-read OrderModel $order
 * @method PaymentEntityModel toEntity()
 * @method PaymentFactory factory($count = null, $state = [])
 */
class PaymentModel extends BaseModel
{
    use HasFactory;
    use PaymentProps;

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

    public function getGuarded(): array
    {
        $p = PaymentEntityModel::props();
        return collect($p->toArray())->except([$p->id, $p->created_at])->toArray();
    }
}
