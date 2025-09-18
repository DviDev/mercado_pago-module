<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\MercadoPago\Entities\WebhookNotification\WebhookNotificationEntityModel;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mp_webhook_notifications', function (Blueprint $table) {
            $p = WebhookNotificationEntityModel::props(null, true);
            $table->id();
            $table->string($p->action)->nullable();
            $table->char($p->api_version)->nullable();
            $table->bigInteger($p->data_id)->nullable()->unsigned();
            $table->string($p->date_created)->nullable();
            $table->bigInteger($p->mp_id)->nullable()->unsigned();
            $table->boolean($p->live_mode)->unsigned()->nullable();
            $table->char($p->type)->nullable();
            $table->bigInteger($p->user_id)->nullable()->unsigned();

            $table->timestamp($p->created_at)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mp_webhook_notifications');
    }
};
