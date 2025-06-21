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
    public function __construct(
        public ?PaymentModel $payment = null,
        public $notification = null
    ) {}

    public function checkStatus(): bool
    {
        if (config('mercadopago.debug.webhook.payment')) {
            \Log::info('Payment Status: '.$this->payment->status);
        }

        if ($this->canceled()) {
            return true;
        }

        if ($this->pending()) {
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

        Log::error('Order Status ----------------------------------------------------------------');

        $superAdmin = User::where('email', env('EMAIL_SUPER_ADMIN'))->first();
        $title = "Status {$this->payment->status} sem tratamento";
        $description = 'Verificar tratamento para o status '.$this->payment->status;

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
        if ($this->payment->status !== OrderStatusTypeEnum::canceled->name) {
            return false;
        }

        $order = $this->payment->order;
        if ($order->isLastStatus(OrderStatusTypeEnum::canceled)) {
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
        if (config('base.local_testing_production') == true) {
            return;
        }
        $notificaton = $this->notification ?? new NotificationInvoice($order);
        $notificaton->title = $title;
        $notificaton->description = $description;

        $order->user->notify($notificaton);
    }

    public function pending(): bool
    {
        if ($this->payment->status !== 'pending') {
            return false;
        }
        $order = $this->payment->order;
        if ($order->isLastStatus(OrderStatusTypeEnum::in_process)) {
            return false;
        }
        if ($order->isLastStatus(OrderStatusTypeEnum::paid)) {
            return false;
        }

        $description = $this->payment->getDescription();
        $this->addStatus($order, OrderStatusTypeEnum::in_process, $description);

        $this->notify($order, 'Estamos aguardando o pagamento!', $description);

        return true;
    }

    protected function inProcess(): bool
    {
        if ($this->payment->status != OrderStatusTypeEnum::in_process->name) {
            return false;
        }

        $order = $this->payment->order;
        if ($order->isLastStatus(OrderStatusTypeEnum::in_process)) {
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
        if ($this->payment->status !== OrderStatusTypeEnum::rejected->name) {
            return false;
        }

        $order = $this->payment->order;

        if ($order->isLastStatus(OrderStatusTypeEnum::rejected)) {
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

        $this->notify($order, 'O pagamento da ordem '.$order->id.' no valor de '.$this->payment->transaction_amount.' foi aprovado!', $description);

        return true;
    }

    public function getPayment($order_id, $payment_id, $description, $notification_id = null): ?PaymentModel
    {
        $p = PaymentEntityModel::props();

        $access_token = config('mercadopago.access_token');
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

        $payment->description = $description.'-'.$data['description'];
        $payment->order_id = $order_id;
        $payment->notification_id = $notification_id ?? null;
        $payment->point_of_interaction_type = $data['point_of_interaction']['type'];
        $payment->save();

        $payment->card()->create([
            'cardholder_identification_number' => $data['card']['cardholder']['identification']['number'],
            'cardholder_identification_type' => $data['card']['cardholder']['identification']['type'],
            'cardholder_name' => $data['card']['cardholder']['name'],
            'expiration_month' => $data['card']['expiration_month'],
            'expiration_year' => $data['card']['expiration_year'],
            'first_six_digits' => $data['card']['first_six_digits'],
            'last_four_digits' => $data['card']['last_four_digits'],
            'created_at' => $data['date_created'],
            'updated_at' => $data['date_last_updated'],
        ]);

        return $payment;
    }
}
