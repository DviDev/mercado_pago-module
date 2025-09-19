<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\MercadoPago\Entities\PaymentCard\PaymentCardEntityModel;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mp_payment_cards', function (Blueprint $table) {
            $p = PaymentCardEntityModel::props(force: true);
            $table->id();
            $table->foreignId($p->payment_id)->references('id')->on('mp_payments')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->string('cardholder_identification_number')->nullable();
            $table->string('cardholder_identification_type')->nullable();
            $table->string('cardholder_name')->nullable();
            $table->smallInteger('expiration_month')->nullable();
            $table->smallInteger('expiration_year')->nullable();
            $table->mediumInteger('first_six_digits')->nullable();
            $table->smallInteger('last_four_digits')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mp_payment_cards');
    }
};
