<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\MercadoPago\Entities\Payment\PaymentEntityModel;

return new class extends Migration
{

    public function up()
    {
        Schema::table('mp_payments', function (Blueprint $table) {
            $p = PaymentEntityModel::props(null, true);
            $table->foreignId($p->url_notification_id)->unsigned()->nullable()->after($p->notification_id)
                ->references('id')->on('mp_back_url_notifications')->cascadeOnUpdate()->restrictOnDelete();
        });
    }


    public function down()
    {
        Schema::table('', function (Blueprint $table) {});
    }
};
