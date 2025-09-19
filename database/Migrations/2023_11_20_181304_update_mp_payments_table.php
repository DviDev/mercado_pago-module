<?php

declare(strict_types=1);

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
        Schema::table('mp_payments', function (Blueprint $table): void {
            $p = PaymentEntityModel::props(force: true);
            $table->string($p->point_of_interaction_type);
            $table->string($p->point_of_interaction_transaction_qr_code)->nullable();
            $table->string($p->point_of_interaction_transaction_ticket_url)->nullable();
            $table->string($p->transaction_details_external_resource_url)->after($p->transaction_details_total_paid_amount)->nullable();
            $table->string($p->transaction_details_digitable_line)->after($p->transaction_details_external_resource_url)->nullable();
            $table->string($p->transaction_details_barcode_content)->after($p->transaction_details_digitable_line)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mp_payments', function (Blueprint $table): void {
            $table->dropColumn('point_of_interaction_type');
            $table->dropColumn('point_of_interaction_transaction_qr_code');
            $table->dropColumn('point_of_interaction_transaction_ticket_url');
            $table->dropColumn('transaction_details_external_resource_url');
            $table->dropColumn('transaction_details_digitable_line');
            $table->dropColumn('transaction_details_barcode_content');
        });
    }
};
