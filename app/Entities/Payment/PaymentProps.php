<?php

declare(strict_types=1);

namespace Modules\MercadoPago\Entities\Payment;

/**
 * @property $id
 * @property $mp_id
 * @property $collector_id
 * @property $external_reference
 * @property $date_approved
 * @property $date_created
 * @property $description
 * @property $installments
 * @property $operation_type
 * @property $status
 * @property $status_detail
 * @property $transaction_amount
 * @property $transaction_details_installment_amount
 * @property $transaction_details_net_received_amount
 * @property $transaction_details_total_paid_amount
 * @property $transaction_details_external_resource_url
 * @property $transaction_details_digitable_line
 * @property $transaction_details_barcode_content
 * @property $payment_method_id
 * @property $payment_type_id
 * @property $notification_id
 * @property $url_notification_id
 * @property $order_id
 * @property $created_at
 * @property $point_of_interaction_type
 * @property $point_of_interaction_transaction_qr_code
 * @property $point_of_interaction_transaction_ticket_url
 */
trait PaymentProps {}
