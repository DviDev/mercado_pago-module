<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Domains;

use Closure;
use Illuminate\Support\Facades\Log;
use Modules\Base\Domain\BaseDomain;
use Modules\MercadoPago\Entities\Payment\PaymentEntityModel;
use Modules\MercadoPago\Entities\WebhookNotification\WebhookNotificationEntityModel;
use Modules\MercadoPago\Models\PaymentModel;
use Modules\MercadoPago\Models\PreferenceModel;
use Modules\MercadoPago\Models\WebhookNotificationModel;
use Modules\MercadoPago\Repositories\PaymentRepository;
use Modules\MercadoPago\Services\HttpPaymentService;
use Modules\Store\Entities\Order\OrderEntityModel;
use Modules\Store\Entities\OrderStatus\OrderStatusEntityModel;
use Modules\Store\Entities\OrderStatusType\OrderStatusTypeEnum;
use Modules\Store\Models\OrderModel;
use Modules\Store\Models\OrderStatusModel;

/**
 * @author Davi Menezes (davimenezes.dev@gmail.com)
 * @copyright Copyright (c) 2022.
 *
 * @link https://github.com/DaviMenezes
 *
 * @method PaymentRepository repository()
 */
final class PaymentDomain extends BaseDomain
{
    public function repositoryClass(): string
    {
        return PaymentRepository::class;
    }

    public function createViaApi($payment_id, OrderModel $order): PaymentModel|bool
    {
        $config = $this->getConfig($order);

        $data = (new HttpPaymentService($config))->run($payment_id);
        if ($data->failed()) {
            Log::error('Mercado pago - getPayment: Erro ao tentar se comunicar. '.$data->body());

            return false;
        }

        $payment = PaymentModel::makeViaApiData($data->json());
        $payment->order_id = $order->id;
        $payment->save();

        return $payment;
    }

    public function getConfig(OrderModel $order): ?string
    {
        return config('mercadopago.access_token');
    }

    public function checkInProgress(OrderModel $order, PreferenceModel $preference)
    {
        $payment = $preference->order->payment;
        $data = (new HttpPaymentService($this->getConfig($order)))->run($payment->id);

        $paymentData = PaymentModel::makeViaApiData($data);
        $paymentDB = (new PaymentRepository)->find($payment->id);
        if (in_array($paymentDB->status, [OrderStatusTypeEnum::paid, OrderStatusTypeEnum::rejected])) {
            return;
        }
        if ($paymentDB->status === OrderStatusTypeEnum::in_process->name && $paymentData->status !==
            OrderStatusTypeEnum::in_process->name) {
            $paymentDB->status = $paymentData->status;
            $paymentDB->status_detail = $paymentData->status_detail;
            $paymentDB->save();

            $paymentDB->order->addStatus(OrderStatusTypeEnum::fromName($paymentData->status), $paymentData->getDescription());
        }
    }

    public function checkWebhookNotifications($date)
    {
        Log::info('Iniciando verificação de falta de pgto(mp) para algum pedido');

        $p = WebhookNotificationEntityModel::props();
        WebhookNotificationModel::query()
            ->select($p->id, $p->data_id)
            ->whereDate($p->created_at, '>=', $date)
            ->groupBy($p->id, $p->data_id)
            ->each(function (WebhookNotificationModel $m) {
                $data = (new HttpPaymentService(config('mercadopago.access_token')))->run($m->data_id);
                $external_reference = $data->json('external_reference');
                $order_id = str($external_reference)->explode('-')->last();

                $p = PaymentEntityModel::props();
                $payment = PaymentModel::query()
                    ->where($p->order_id, $order_id)
                    ->where($p->mp_id, $m->data_id);

                if (! $payment->exists()) {
                    Log::error('nao existe pgto de ordem '.$order_id.' e mp_id '.$m->data_id);
                }
            });
        Log::info('FIM de checkWebhookNotifications');
    }

    public function checkPaymentsOrderId(Closure $fn)
    {
        Log::info('-- Pagamentos(mp) que precisam atualizar id do pedido');
        $p = PaymentEntityModel::props();
        $builder = PaymentModel::query()
            ->whereDate($p->date_created, '>=', '2023-03-20')
            ->orderBy($p->id);
        if ($builder->count() === 0) {
            Log::info('-- Não há ocorrências');

            return;
        }
        $has_occurrence = false;
        $builder->each(function (PaymentModel $payment) use ($fn, &$has_occurrence) {
            $config = $this->getConfig($payment->order);
            $data = (new HttpPaymentService($config))->run($payment->mp_id);
            $external_reference = $data->json('external_reference');
            $order_id = (int) str($external_reference)->explode('-')->last();
            if ($payment->order_id !== $order_id) {
                $has_occurrence = true;
                $str = "UPDATE `mp_payments` SET `order_id`={$order_id} WHERE `id`= $payment->id;";
                Log::info($str);
                $fn($payment, $order_id);
            }
        });
        if (! $has_occurrence) {
            Log::info('-- Não há ocorrências');
        }
        Log::info(str_pad('-- ', 100, '-'));
    }

    public function clearDuplicatePayments(Closure $fn)
    {
        Log::info('-- Eliminar pgtos duplicados');

        $p = PaymentEntityModel::props();
        $builder = PaymentModel::query()
            ->select($p->order_id)
            ->groupBy($p->order_id)
            ->orderBy($p->order_id);
        if ($builder->count() === 0) {
            Log::info('-- Não há ocorrências');

            return;
        }
        $has_occurrence = false;
        $builder->each(function (PaymentModel $model) use ($p, $fn, &$has_occurrence) {
            $white_list_ids = PaymentModel::query()
                ->selectRaw("min($p->id) as id")
                ->where($p->order_id, $model->order_id)
                ->groupBy([$p->mp_id, $p->status])
                ->get()->modelKeys();
            if (count($white_list_ids) === 0) {
                return;
            }

            $ids = PaymentModel::query()->where($p->order_id, $model->order_id)
                ->whereNotIn($p->id, $white_list_ids)->get()->modelKeys();
            if (count($ids) === 0) {
                return;
            }

            $ids = collect($ids)->join(',');

            $fn($model, $p);
            Log::info("-- select t.id, t.mp_id, t.status, t.status_detail, t.date_created, t.created_at from mp_payments t where order_id = {$model->order_id} group by t.id, t.mp_id, t.status, t.date_created, t.created_at order by t.mp_id, t.created_at;");
            Log::info("DELETE FROM {$p->table} WHERE {$p->order_id} = {$model->order_id} AND `id` in ($ids);");

            $has_occurrence = true;
        });
        if (! $has_occurrence) {
            Log::info('-- Não há ocorrências');
        }
        Log::info(str_pad('-- ', 100, '-'));
    }

    public function checkPaymentStatusVsOrderLastStatus(?Closure $fn = null)
    {
        Log::info('-- Verificando status do pgto(mp) com ultimo status do pedido');

        //        Todos os pedidos que tem pagamentos com data maior que 20 03 2023
        //        Pegar os pedidos da ordem, agrupado por mp_id e pegar o ultimo baseado na data de criacao
        $order = OrderEntityModel::props();
        $paymentProps = PaymentEntityModel::props(PaymentModel::table());
        $ocurrences = [];
        OrderModel::query()
            ->whereDate($order->created_at, '>=', '2023-03-20')
            ->each(function (OrderModel $order) use ($paymentProps, $fn, &$ocurrences) {
                PaymentModel::query()->where($paymentProps->order_id, $order->id)
                    ->orderByDesc($paymentProps->date_created)
                    ->orderByDesc($paymentProps->created_at)
                    ->limit(1)->get()
                    ->each(function (PaymentModel $payment) use ($paymentProps, $fn, &$ocurrences) {
                        if ($payment->status === 'approved' && $payment->order->lastStatusEnum() !== OrderStatusTypeEnum::paid) {
                            $ocurrences[$payment->order_id] = true;
                            $fn($payment, $paymentProps);

                            return;
                        }
                        if ($payment->status === 'pending' && $payment->order->lastStatusEnum() !== OrderStatusTypeEnum::pendent) {
                            $ocurrences[$payment->order_id] = true;
                            $fn($payment, $paymentProps);

                            return;
                        }
                        if ($payment->status === 'rejected' && $payment->order->lastStatusEnum() !== OrderStatusTypeEnum::rejected) {
                            $ocurrences[$payment->order_id] = true;
                            $fn($payment, $paymentProps);

                            return;
                        }
                        if ($payment->status === 'in_process' &&
                            ($payment->order->lastStatusEnum() !== OrderStatusTypeEnum::in_process)) {
                            $ocurrences[$payment->order_id] = true;
                            $fn($payment, $paymentProps);

                            return;
                        }
                        if ($payment->status === 'cancelled' &&
                            ($payment->order->lastStatusEnum() !== OrderStatusTypeEnum::canceled)) {
                            $ocurrences[$payment->order_id] = true;
                            $fn($payment, $paymentProps);
                        }
                    });
            });
        if (count($ocurrences) === 0) {
            Log::info('-- Não há ocorrência');
        }
        Log::info(str_pad('-- ', 100, '-'));
    }

    public function checkOrderStatusWithDuplicatedPaidStatus(Closure $fn)
    {
        Log::info('-- Verificando pedidos com status de pago duplicados');

        $statusP = OrderStatusEntityModel::props();
        OrderStatusModel::query()->select($statusP->order_id)
            ->selectRaw("count($statusP->type_id)")
            ->where($statusP->type_id, OrderStatusTypeEnum::paid->value)
            ->groupBy($statusP->order_id)
            ->havingRaw("count($statusP->type_id) > ".OrderStatusTypeEnum::paid->value)
            ->orderBy($statusP->order_id)
            ->each(function (OrderStatusModel $model) use ($fn, $statusP) {
                $order = OrderModel::find($model->order_id);
                $order->statuses()->where($statusP->type_id, OrderStatusTypeEnum::paid->value)
                    ->each(function (OrderStatusModel $status) use ($fn, $statusP) {
                        $fn($status, $statusP);
                        Log::info("-- SELECT * FROM store_order_status WHERE order_id={$status->order_id};");
                        Log::info("DELETE FROM `store_order_status` WHERE order_id={$status->order_id} AND `id`=$status->id;");
                    });
            });
        Log::info(str_pad('-- ', 100, '-'));
    }
}
