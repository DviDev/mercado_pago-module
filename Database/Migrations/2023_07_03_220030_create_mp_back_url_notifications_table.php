<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\MercadoPago\Entities\UrlNotification\UrlNotificationEntityModel;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mp_back_url_notifications', function (Blueprint $table) {
            $p = UrlNotificationEntityModel::props(null, true);
            $table->id();
            $table->bigInteger($p->collection_id)->unsigned()->nullable();
            $table->char($p->collection_status)->nullable();
            $table->bigInteger($p->payment_id)->unsigned()->nullable();
            $table->char($p->status)->nullable();
            $table->string($p->external_reference)->nullable();
            $table->char($p->payment_type)->nullable();
            $table->bigInteger($p->merchant_order_id)->unsigned()->nullable();
            $table->foreignId($p->preference_id)->nullable()->references('id')->on('mp_preferences')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->char($p->site_id)->nullable();
            $table->char($p->processing_mode)->nullable();
            $table->string($p->merchant_account_id)->nullable();
            $table->timestamp($p->created_at)->nullable();

            $table->comment('guarda parametros das urls de retorno do mp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mp_back_url_notifications');
    }
};
