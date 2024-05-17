<?php

namespace Modules\MercadoPago\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Modules\MercadoPago\Entities\Payment\PaymentEntityModel;
use Modules\MercadoPago\Models\PaymentModel;
use Modules\MercadoPago\Notifications\PaymentStatusNotification;
use Modules\Store\Entities\OrderStatusType\OrderStatusTypeEnum;
use Modules\Store\Models\OrderModel;
use Modules\Store\Notifications\NotificationInvoice;

class OrderStatusService
{
    public function __construct(public PaymentModel|null $payment = null)
    {
    }

    public function checkStatus(): bool
    {
        if ($this->canceled()) {
            return true;
        }

        if ($this->pixPending()) {
            return true;
        }

        if ($this->inProcess()) {
            return true;
        }

        if ($this->reject()) {
            return true;
        }

        if ($this->approved()) {
            return true;
        }

        Log::error("Order Status ----------------------------------------------------------------");

        $superAdmin = User::where('email', config('EMAIL_SUPER_ADMIN'))->first();
        $title = "Status {$this->payment->status} sem tratamento";
        $description = "Verificar tratamento para o status " . $this->payment->status;

        Log::error($title);
        Log::error($description);

        $superAdmin->notify(new PaymentStatusNotification(
            title: $title,
            description: $description
        ));

        return false;
    }

    private function canceled(): bool
    {
        if ($this->payment->status !== 'cancelled') {
            return false;
        }

        $order = $this->payment->order;
        if ($order->lastStatus() && $order->lastStatusEnum() == OrderStatusTypeEnum::canceled) {
            return false;
        }

        $description = $this->payment->getDescription();
        $this->addStatus($order, OrderStatusTypeEnum::canceled, $description);

        $this->notify($order, 'O pagamento foi cancelado', $description);

        return true;
    }

    protected function addStatus(OrderModel $order, $status, ?string $description)
    {
        return $order->addStatus($status, $description);
    }

    protected function notify(OrderModel $order, ?string $title, ?string $description = null): void
    {
        if (config('app.local_testing_production') == true) {
            return;
        }
        $order->user->notify(new NotificationInvoice(
            $order,
            $title,
            $description,
        ));
    }

    public function pixPending(): bool
    {
        if ($this->payment->status !== 'pending') {
            return false;
        }
        $order = $this->payment->order;
        if ($order->lastStatus() && $order->lastStatusEnum() == OrderStatusTypeEnum::in_process) {
            return false;
        }
        if ($order->lastStatus() && $order->lastStatusEnum() == OrderStatusTypeEnum::paid) {
            return false;
        }

        $description = $this->payment->getDescription();
        $this->addStatus($order, OrderStatusTypeEnum::in_process, $description);

        $this->notify($order, 'Estamos aguardando a transferência do pix!', $description);

        return true;
    }

    protected function inProcess(): bool
    {
        if ($this->payment->status != 'in_process') {
            return false;
        }

        $order = $this->payment->order;
        if ($order->lastStatus() && $order->lastStatusEnum() == OrderStatusTypeEnum::in_process) {
            return false;
        }

        $this->addStatus($order, OrderStatusTypeEnum::in_process, 'O pagamento está sendo processado');

        $this->notify(
            $order,
            'Estamos processando seu pagamento',
            'Informaremos em breve por e-mail se foi aprovado.'
        );

        return true;
    }

    protected function reject(): bool
    {
        if ($this->payment->status !== 'rejected') {
            return false;
        }

        $order = $this->payment->order;

        if ($order->lastStatus() && $order->lastStatusEnum() == OrderStatusTypeEnum::rejected) {
            return false;
        }

        $description = $this->payment->getDescription();
        $this->addStatus($order, OrderStatusTypeEnum::rejected, $description);

        $this->notify($order, 'O pagamento foi rejeitado');

        return true;
    }

    protected function approved(): bool
    {
        if ($this->payment->status !== 'approved') {
            return false;
        }

        $order = $this->payment->order;
        if ($order->lastStatus() && $order->lastStatusEnum() == OrderStatusTypeEnum::paid) {
            Log::info("A ordem $order->id já está paga.");
            return true;
        }

        $description = $this->payment->getDescription();
        $this->addStatus($order, OrderStatusTypeEnum::paid, $description);

        $this->notify($order, 'O pagamento da ordem ' . $order->id . ' no valor de ' . $this->payment->transaction_amount . ' foi aprovado!', $description);

        return true;
    }

    public function getPayment($order_id, $payment_id, $description, $notification_id = null): ?PaymentModel
    {
        $p = PaymentEntityModel::props();

        $access_token = config("mercadopago.access_token");
        $data = (new HttpPaymentService($access_token))->run($payment_id);
        if ($data->failed()) {
            return null;
        }
        $data = $data->json();

        if ($payment = PaymentModel::query()->where([
            $p->mp_id => $data[$p->id],
            $p->status => $data[$p->status],
            $p->status_detail => $data[$p->status_detail],
            $p->payment_type_id => $data[$p->payment_type_id],
            $p->order_id => $order_id,
        ])->get()->first()) {
            return $payment;
        }
        $payment = PaymentModel::makeViaApiData($data);

        $payment->description = $description . '-' . $data['description'];
        $payment->order_id = $order_id;
        $payment->notification_id = $notification_id ?? null;
        $payment->point_of_interaction_type = $data['point_of_interaction']['type'];
        $payment->save();

        return $payment;
    }
}
