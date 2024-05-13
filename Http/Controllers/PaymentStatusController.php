<?php

namespace Modules\MercadoPago\Http\Controllers;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Modules\MercadoPago\Domains\PaymentDomain;
use Modules\MercadoPago\Entities\Payment\PaymentEntityModel;
use Modules\MercadoPago\Entities\Preference\PreferenceEntityModel as Preference;
use Modules\MercadoPago\Entities\UrlNotification\UrlNotificationEntityModel;
use Modules\MercadoPago\Models\PaymentModel;
use Modules\MercadoPago\Models\PreferenceModel;
use Modules\MercadoPago\Models\UrlNotificationModel;
use Modules\MercadoPago\Models\WebhookNotificationModel;
use Modules\MercadoPago\Services\HttpPaymentService;
use Modules\MercadoPago\Services\OrderStatusService;
use Modules\Store\Entities\OrderStatusType\OrderStatusTypeEnum;
use Modules\Store\Entities\PaymentType\PaymentTypeEnum;
use Modules\Store\Models\CartItemModel;
use Modules\Store\Models\CartModel;
use Modules\Store\Models\OrderModel;
use Modules\Store\Notifications\NotificationInvoice;
use Modules\Store\Repositories\OrderRepository;

class PaymentStatusController extends Controller
{
    protected ?PaymentModel $payment;
    protected WebhookNotificationModel|Builder $WebhookNotification;
    private UrlNotificationModel $UrlNotification;

    public function payment()
    {
        $data = \request()->all();
        //when check url in webhook portal of mp
        /*if ($data['type'] == 'test') {
            return response()->json(true);
        }*/

        if (!$this->createWebhookNotification($data)) {
            return response()->json(false, 500);
        }
        //Todo todo o processamento abaixo pode rodar em segundo plano para liberar a requisicao se necessario.
        //Para isso um processo de supervisão da fila (como o supervisor) precisa ser configurado
        //Todo devemos guardar as informações do mp e analisar posteriormente
        try {
            $api_data = $this->getApiPaymentData($this->WebhookNotification->data_id);
            $order_id = str($api_data['additional_info']['items'][0]['id'])->explode('#')->first();
            $order = (new OrderRepository())->find($order_id);
            $this->payment = (new OrderStatusService)->getPayment(
                $order,
                $this->WebhookNotification->data_id,
                $this->WebhookNotification->id);

            if (config('mercadopago.debug.webhook.payment')) {
                Log::info('MERCADOPAGO.DEBUG.WEBHOOK.PAYMENT:...');
                Log::info($this->payment->toJson());
            }

            if ((new OrderStatusService($this->payment))->checkStatus()) {
                return response()->json(true);
            }

            Log::error('O status não está em progresso, nem rejeitado, nem aprovado. Analisar.');
            Log::info(request()->json());

            return response()->json(true);
        } catch (\Exception $exception) {
            Log::error('Erro ao processar webhook do mercado pago');
            Log::info(json_encode($data));
            Log::info($exception->getMessage() . ' in ' . $exception->getFile() . ' line ' . $exception->getLine());
            Log::info($exception);
            //Se chegou até aqui o problema não é mais do mercado pago é do nosso processamento que precisa ser
            // analisado em outro momento
            return response(true);
        }
    }

    protected function createWebhookNotification(array $data): bool
    {
        try {
            $data['data_id'] = $data['data']['id'];
            $data['mp_id'] = $data['id'];
            $data['live_mode'] = (boolean)$data['live_mode'];

            $this->WebhookNotification = WebhookNotificationModel::query()
                ->updateOrCreate([
                    'action' => $data['action'],
                    'data_id' => $data['data_id'],
                    'mp_id' => $data['id'],
                    'user_id' => $data['user_id'],
                ], $data);

            if (config('mercadopago.debug.webhook.notification')) {
                Log::info('MercadoPago Webhook Log: ...');
                Log::info($this->WebhookNotification->toJson());
            }

            return true;
        } catch (Exception $exception) {
            Log::error('Houve um erro ao gravar retorno do de webhook do mercado pago');
            Log::info(json_encode($data));
            Log::info($exception->getMessage());
            Log::debug($exception);
            return false;
        }
    }

    protected function getApiPaymentData($payment_id): array
    {
        $access_token = config("mercadopago.access_token");
        $data = (new HttpPaymentService($access_token))->run($payment_id);
        if (!$data->failed()) {
            return $data->json();
        }
        $msg = "MercadoPago: Registro {$this->WebhookNotification->data_id} não encontrado.";
        if (config('app.env') == 'local') {
            $msg .= ' token:' . config("mercadopago.access_token");
        }
        throw new Exception($msg);
    }

    /**
     * @return void
     */
    protected function createPayment(OrderModel $order, $payment_id): bool
    {
        $p = PaymentEntityModel::props();

        $access_token = (new PaymentDomain())->getConfig($order);
        $data = (new HttpPaymentService($access_token))->run($payment_id)->json();

        $arr = [
            $p->mp_id => $data[$p->id],
            $p->status => $data[$p->status],
            $p->status_detail => $data[$p->status_detail],
            $p->payment_type_id => $data[$p->payment_type_id],
            $p->order_id => $order->id,
        ];
        if (isset($this->WebhookNotification->id)) {
            $arr[$p->notification_id] = $this->WebhookNotification->id;
        }
        if (isset($this->UrlNotification->id)) {
            $arr[$p->url_notification_id] = $this->UrlNotification->id;
        }
        if ($this->payment = PaymentModel::query()->where($arr)->get()->first()) {
            return true;
        }

        $this->payment = PaymentModel::makeViaApiData($data);
        $this->payment->order_id = $order->id;
        $this->payment->notification_id = $this->WebhookNotification->id ?? null;
        $this->payment->url_notification_id = $this->UrlNotification->id ?? null;
        $this->payment->save();

        return true;
    }

    public function success(OrderModel $order)
    {
        $data = collect(request()->all())->map(fn($value) => $value == 'null' ? null : $value)->all();
        $data['preference_id'] = PreferenceModel::getByStringId($data['preference_id'])->id;
        $this->UrlNotification = UrlNotificationModel::query()->create($data);

        try {
            if (!$this->createPayment($order, $data['payment_id'])) {
                session()->flash('error', 'Não foi possível se comunicar com o sistema de pagamentos. Aguarde.');
                session()->flash('only_toastr');
                return redirect()->route('order', $order->id);
            }

            if ($order->lastStatusEnum() == OrderStatusTypeEnum::paid) {
                return redirect()->route('order', $order->id);
            }

            if ($this->payment->status == 'approved') {
                DB::beginTransaction();

                if ($order->lastStatusEnum() == OrderStatusTypeEnum::awaiting_payment) {
                    $status = $order->addStatus(OrderStatusTypeEnum::paid);
                    $status->payment_type_id = PaymentTypeEnum::card->value;
                    $status->save();
                }

                Notification::send(auth()->user(), new NotificationInvoice($order));

                $preference = $this->payment->order->preference;
                $preference->notification_id = $this->UrlNotification->id;
                $preference->save();

                $this->clearCartItems();

                DB::commit();

                session()->flash('success', 'Parabéns seus pagamento foi aprovado.');

                return redirect()->route('order', $order->id);
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            if (config('app.env') == 'local') {
                session()->flash('error', 'O pagamento não pode ser aprovado.');

                throw $exception;
            }
            return redirect()->route('order', $order->id);
        }
    }

    /**
     * @return void
     */
    protected function clearCartItems(): void
    {
        CartModel::getByLoggedUser()->items()->each(function (CartItemModel $cartItem) {
            $cartItem->delete();
        });
    }

    public function pending(OrderModel $order)
    {
        try {
            DB::beginTransaction();

            $data = request()->all();
            $preference = $this->getPreference($data['preference_id']);

            $data['preference_id'] = $preference->id;
            $this->createUrlNotification($data);

            if ($order->payment->id) {
                DB::commit();
                return redirect()->route('orders');
            }
            if (!$this->payment = PaymentModel::getByMpId($data['payment_id'])) {
                $this->createPayment($order, $data['payment_id']);
            }
            $this->payment->save();

            $this->inProcess();

            DB::commit();

            session()->flash('success', 'O pedido está sendo processado.');

            return redirect()->route('order', $this->payment->order->id);
        } catch (\Exception $exception) {
            DB::rollBack();
            if (config('app.env') == 'local') {
                throw $exception;
            }
            session()->flash('error', 'Houve um problema no processamento do pedido. Aguarde.');
            Log::error('Order pending: status:' . OrderStatusTypeEnum::in_process->name . $exception->getMessage());
            return redirect()->route('orders');
        }
    }

    protected function getPreference($preference_id): PreferenceModel
    {
        return PreferenceModel::whereFn(fn(Preference $p) => [
            [$p->mp_preference_id, $preference_id]
        ])->get()->first();
    }

    protected function createUrlNotification($data): void
    {
        $query = UrlNotificationModel::whereFn(fn(UrlNotificationEntityModel $p) => [
            [$p->payment_id, $data['payment_id']]
        ]);
        $this->UrlNotification = $query->exists()
            ? $query->get()->first()
            : UrlNotificationModel::query()->create($data);
    }

    protected function inProcess(): bool
    {
        if ($this->payment->status != 'in_process') {
            return false;
        }

        if ($this->payment->order->lastStatusEnum() == OrderStatusTypeEnum::in_process) {
            return true;
        }

        $this->payment->order->addStatus(OrderStatusTypeEnum::in_process, 'O pagamento está sendo processado');

        auth()->user()->notify(new NotificationInvoice(
            $this->payment->order,
            title: 'Estamos processando seu pagamento',
            description: 'Informaremos em algumas horas por e-mail se foi aprovado.'
        ));

        return true;
    }

    public function failure(OrderModel $order)
    {
        $data = collect(request()->all())->map(fn($value) => $value == 'null' ? null : $value)->all();
        $preference = $this->getPreference($data['preference_id']);
        $data['preference_id'] = $preference->id;

        $this->createUrlNotification($data);

        $this->createPayment($order, $data['payment_id']);

        if ($this->payment->status == 'rejected') {
            try {
                DB::beginTransaction();

                if ($order->lastStatusEnum() !== OrderStatusTypeEnum::rejected) {
                    $this->reject();
                }

                $this->clearCartItems();

                DB::commit();

                session()->flash('error', $this->payment->getDescription() . '. Tente novamente');

                return redirect()->route('order', $order->id);

            } catch (\Exception $exception) {
                DB::rollBack();
                if (config('app.env') == 'local') {
                    throw $exception;
                }
            }
        }
    }

    protected function reject(): bool
    {
        if ($this->payment->status !== 'rejected') {
            return false;
        }

        $description = $this->payment->getDescription();
        $this->payment->order->addStatus(OrderStatusTypeEnum::rejected, $description);

        $preference = $this->payment->order->preference;
        if ($preference->notification_id) {
            return true;
        }

        auth()->user()->notify(new NotificationInvoice($this->payment->order));

        $notification = auth()->user()->notifications()->latest()->first()->id;
        $preference->notification_id = $notification->id;
        $preference->save();

        return true;
    }
}
