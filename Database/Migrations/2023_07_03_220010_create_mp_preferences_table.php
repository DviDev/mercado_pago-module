<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\MercadoPago\Entities\Preference\PreferenceEntityModel;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mp_preferences', function (Blueprint $table) {
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mp_preferences');
    }
};
