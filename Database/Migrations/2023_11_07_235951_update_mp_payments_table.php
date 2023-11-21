<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\MercadoPago\Entities\Payment\PaymentEntityModel;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mp_payments', function (Blueprint $table) {
            $p = PaymentEntityModel::props(force: true);
            $table->string($p->external_reference)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mp_payments', function (Blueprint $table) {

        });
    }
};
