<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\MercadoPago\Entities\Payment\PaymentEntityModel;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mp_payments', function (Blueprint $table) {
            $p = PaymentEntityModel::props(force: true);
            $table->string($p->point_of_interaction_type);
            $table->string($p->point_of_interaction_transaction_qr_code);
            $table->string($p->point_of_interaction_transaction_ticket_url);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mp_payments', function (Blueprint $table) {
            $table->dropColumn('point_of_interaction_type');
            $table->dropColumn('point_of_interaction_transaction_qr_code');
            $table->dropColumn('point_of_interaction_transaction_ticket_url');
        });
    }
};
