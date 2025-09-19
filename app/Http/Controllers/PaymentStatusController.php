<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Http\Controllers;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notification;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

    protected NotificationInvoice $paymentNotification;

    protected ?OrderModel $orderModel;

    private UrlNotificationModel $UrlNotification;

    public function payment()
    {
        \Log::info('Status mercado pago:...');
        $data = \request()->all();
        $data['xSignature'] = request()->header('x-signature');
        $data['xRequestId'] = request()->header('x-request-id');
        \Log::info(json_encode($data));

        if (! $this->validateMercadoPagoSecretKey()) {
            \Log::info('A chave secreta do MP não é valida');

            return response('👎🏻', 404);
        }

        // when check url in webhook portal of mp
        if ($data['type'] === 'test') {
            return response()->json(true);
        }

        if (! $this->createWebhookNotification($data)) {
            return response()->json(false, 500);
        }
        // Todo todo o processamento abaixo pode rodar em segundo plano para liberar a requisicao se necessario.
        // Para isso um processo de supervisão da fila (como o supervisor) precisa ser configurado
        // Todo devemos guardar as informações do mp e analisar posteriormente
        try {
            $order_id = null;

            $api_data = $this->getApiPaymentData($this->WebhookNotification->data_id);
            $order_id = $this->getOrderId($api_data, $order_id);

            if (! $order_id) {
                Log::error('Mercado Pago: Não foi possível encontrar o id do pedido');

                return response()->json(false, 500);
            }
            $this->orderModel = (new OrderRepository)->find($order_id);

            if ($this->orderModel) {
                $this->payment = (new OrderStatusService)->getPayment(
                    order_id: $this->orderModel->id,
                    payment_id: $this->WebhookNotification->data_id,
                    description: $this->getDescription($this->orderModel),
                    notification_id: $this->WebhookNotification->id);

                if (config('mercadopago.debug.webhook.payment')) {
                    Log::info('MERCADOPAGO.DEBUG.WEBHOOK.PAYMENT:...');
                    Log::info($this->payment->toJson());
                }

                $orderStatusService = new OrderStatusService(payment: $this->payment, notification: $this->paymentNotification);
                if ($orderStatusService->checkStatus()) {
                    if ($this->payment->status === 'approved') {
                        CartModel::clearOrderItems($this->orderModel);
                    }

                    return response()->json(true);
                }
            } else {
                Log::error('Mercado Pago: O pedido '.$order_id.' não foi encontrado');
                Log::error('Api data: '.json_encode($api_data));

                return response()->json(false, 500);
            }

            Log::error('O status '.$this->payment->status.' não está em progresso, nem rejeitado, nem aprovado. Analisar.');
            Log::error('Api data: '.json_encode($api_data));

            return response()->json(true);
        } catch (Exception $exception) {
            Log::error('Erro ao processar webhook do mercado pago');
            Log::info(json_encode($data));
            Log::info($exception->getMessage().' in '.$exception->getFile().' line '.$exception->getLine());
            Log::info($exception);

            // Se chegou até aqui o problema não é mais do mercado pago é do nosso processamento que precisa ser
            // analisado em outro momento
            return response(true);
        }
    }

    public function success(OrderModel $order)
    {
        $data = collect(request()->all())->map(fn ($value) => $value === 'null' ? null : $value)->all();
        $data['preference_id'] = PreferenceModel::getByStringId($data['preference_id'])->id;
        $this->UrlNotification = UrlNotificationModel::query()->create($data);

        try {
            if (! $this->createPayment($order, $data['payment_id'])) {
                session()->flash('error', 'Não foi possível se comunicar com o sistema de pagamentos. Aguarde.');
                session()->flash('only_toastr');

                return redirect()->route('order', $order->id);
            }

            if ($order->lastStatusEnum() === OrderStatusTypeEnum::paid) {
                return redirect()->route('order', $order->id);
            }

            if ($this->payment->status === 'approved') {
                DB::beginTransaction();

                if ($order->lastStatusEnum() === OrderStatusTypeEnum::awaiting_payment) {
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
        } catch (Exception $exception) {
            DB::rollBack();
            if (config('app.env') === 'local') {
                session()->flash('error', 'O pagamento não pode ser aprovado.');

                throw $exception;
            }

            return redirect()->route('order', $order->id);
        }
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

                return redirect()->route('store.store_orders.list');
            }
            if (! $this->payment = PaymentModel::getByMpId($data['payment_id'])) {
                $this->createPayment($order, $data['payment_id']);
            }
            $this->payment->save();

            $this->inProcess();

            DB::commit();

            session()->flash('success', 'O pedido está sendo processado.');

            return redirect()->route('order', $this->payment->order->id);
        } catch (Exception $exception) {
            DB::rollBack();
            throw_if(config('app.env') === 'local', $exception);

            session()->flash('error', 'Houve um problema no processamento do pedido. Aguarde.');
            Log::error('Order pending: status:'.OrderStatusTypeEnum::in_process->name.$exception->getMessage());

            return redirect()->route('store.store_orders.list');
        }
    }

    public function failure(OrderModel $order)
    {
        $data = collect(request()->all())->map(fn ($value) => $value === 'null' ? null : $value)->all();
        $preference = $this->getPreference($data['preference_id']);
        $data['preference_id'] = $preference->id;

        $this->createUrlNotification($data);

        $this->createPayment($order, $data['payment_id']);

        if ($this->payment->status === 'rejected') {
            try {
                DB::beginTransaction();

                if ($order->lastStatusEnum() !== OrderStatusTypeEnum::rejected) {
                    $this->reject();
                }

                $this->clearCartItems();

                DB::commit();

                session()->flash('error', $this->payment->getDescription().'. Tente novamente');

                return redirect()->route('order', $order->id);
            } catch (Exception $exception) {
                DB::rollBack();

                throw_if(config('app.env') === 'local', $exception);
            }
        }
    }

    protected function createWebhookNotification(array $data): bool
    {
        try {
            $data['data_id'] = $data['data']['id'];
            $data['mp_id'] = $data['id'];
            $data['live_mode'] = (bool) $data['live_mode'];

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
        $access_token = config('mercadopago.access_token');
        $data = (new HttpPaymentService($access_token))->run($payment_id);
        if (! $data->failed()) {
            return $data->json();
        }
        $msg = "MercadoPago: Registro {$this->WebhookNotification->data_id} não encontrado.";
        if (config('app.env') === 'local') {
            $msg .= ' token:'.config('mercadopago.access_token');
        }
        throw new Exception($msg);
    }

    /**
     * @return void
     */
    protected function createPayment(OrderModel $order, $payment_id): bool
    {
        $p = PaymentEntityModel::props();

        $access_token = (new PaymentDomain)->getConfig($order);
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

    protected function clearCartItems(): void
    {
        CartModel::getByLoggedUser()->items()->each(function (CartItemModel $cartItem): void {
            $cartItem->delete();
        });
    }

    protected function getPreference($preference_id): PreferenceModel
    {
        return PreferenceModel::whereFn(fn (Preference $p) => [
            [$p->mp_preference_id, $preference_id],
        ])->get()->first();
    }

    protected function createUrlNotification($data): void
    {
        $query = UrlNotificationModel::whereFn(fn (UrlNotificationEntityModel $p) => [
            [$p->payment_id, $data['payment_id']],
        ]);
        $this->UrlNotification = $query->exists()
            ? $query->get()->first()
            : UrlNotificationModel::query()->create($data);
    }

    protected function inProcess(): bool
    {
        if ($this->payment->status !== 'in_process') {
            return false;
        }

        if ($this->payment->order->lastStatusEnum() === OrderStatusTypeEnum::in_process) {
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

    protected function getDescription(OrderModel $order): string
    {
        $items = $order->items()->get('product_id')->pluck('product_id')->join(',');

        return 'ORDER['.$items.']';
    }

    protected function getOrderId(array $api_data, $order_id)
    {
        if (! isset($api_data['additional_info']['items'])) {
            if (in_array($api_data['payment_method_id'], ['pix', 'bolbradesco'])) {
                $order_id = str($api_data['external_reference'])->explode('order-')->pop();
            }
        } else {
            $order_id = str($api_data['additional_info']['items'][0]['id'])->explode('#')->first();
        }
        if (is_int($order_id)) {
            return $order_id;
        }
        if (is_string($order_id)) {
            $order_id = str($order_id)->explode(':')->pop();
        }

        return $order_id;
    }

    /**
     * @see https://www.mercadopago.com.br/developers/pt/docs/your-integrations/notifications/webhooks
     */
    protected function validateMercadoPagoSecretKey(): bool
    {
        // Obtain the x-signature value from the header
        $xSignature = request()->header('x-signature');
        $xRequestId = request()->header('x-request-id');

        // Obtain Query params related to the request URL

        // Extract the "data.id" from the query params
        $dataID = request('data.id');
        if (! $dataID) {
            return false;
        }
        // Obtain the secret key for the user/application from Mercadopago developers site
        $secret = config('mercadopago.webhook_secret_key');

        if (! $secret) {
            return false;
        }

        // Separating the x-signature into parts
        $parts = explode(',', $xSignature);

        // Initializing variables to store ts and hash
        $ts = null;
        $hash = null;

        // Iterate over the values to obtain ts and v1
        foreach ($parts as $part) {
            // Split each part into key and value
            $keyValue = explode('=', $part, 2);
            if (count($keyValue) === 2) {
                $key = trim($keyValue[0]);
                $value = trim($keyValue[1]);
                if ($key === 'ts') {
                    $ts = $value;
                } elseif ($key === 'v1') {
                    $hash = $value;
                }
            }
        }

        if (! $ts || ! $hash) {
            return false;
        }

        // Generate the manifest string
        $manifest = "id:$dataID;request-id:$xRequestId;ts:$ts;";

        // Create an HMAC signature defining the hash type and the key as a byte array
        $sha = hash_hmac('sha256', $manifest, $secret);

        if ($sha !== $hash) {
            return false; // HMAC verification failed
        }

        return true;
    }
}
