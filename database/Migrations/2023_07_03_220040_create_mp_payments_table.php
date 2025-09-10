<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\MercadoPago\Entities\Payment\PaymentEntityModel;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mp_payments', function (Blueprint $table) {
            $p = PaymentEntityModel::props(null, true);
            $table->id();

            $table->bigInteger($p->mp_id);
            $table->bigInteger($p->collector_id);
            $table->char($p->payment_method_id);
            $table->char($p->payment_type_id);
            $table->foreignId($p->notification_id)->nullable()->references('id')->on('mp_webhook_notifications')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('preference_id')->nullable()->references('id')->on('mp_preferences')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreignId($p->order_id)->nullable()->references('id')->on('store_orders')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->string($p->date_approved)->nullable();
            $table->string($p->date_created)->nullable();
            $table->string($p->description);
            $table->char($p->installments);
            $table->char($p->operation_type);
            $table->char($p->status);
            $table->string($p->status_detail);
            $table->decimal($p->transaction_amount)->unsigned();
            $table->decimal($p->transaction_details_installment_amount)->unsigned()->nullable();
            $table->decimal($p->transaction_details_net_received_amount)->unsigned()->nullable();
            $table->decimal($p->transaction_details_total_paid_amount)->unsigned()->nullable();

            $table->timestamp($p->created_at)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mp_payments');
    }
};
