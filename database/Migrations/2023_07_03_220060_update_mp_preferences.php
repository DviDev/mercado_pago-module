<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\MercadoPago\Entities\Preference\PreferenceEntityModel;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mp_preferences', function (Blueprint $table) {
            $p = PreferenceEntityModel::props(null, true);
            $table->bigInteger($p->collector_id)->unsigned()->change();
            $table->bigInteger($p->client_id)->unsigned()->change();
            $table->dropColumn('payment_id');
        });
    }

    public function down()
    {
        Schema::table('', function (Blueprint $table) {});
    }
};
