<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\MercadoPago\Entities\Preference\PreferenceEntityModel;

return new class extends Migration
{
    public function up()
    {
        Schema::create('mp_preferences', function (Blueprint $table): void {
            $p = PreferenceEntityModel::props(null, true);
            $table->id();
            $table->foreignId($p->user_id)->references('id')->on('users')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->foreignId($p->order_id)->references('id')->on('store_orders')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->string($p->mp_preference_id);
            $table->bigInteger($p->collector_id);
            $table->bigInteger($p->client_id);
            $table->bigInteger('payment_id')->nullable();
            $table->uuid($p->notification_id)->nullable()->references('id')->on('notifications')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->timestamp($p->created_at)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('mp_preferences');
    }
};
