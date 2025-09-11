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
            $p = PreferenceEntityModel::props(force: true);
            $table->string($p->mp_preference_id)->nullable()->change();
            $table->string($p->collector_id)->nullable()->change();
            $table->string($p->client_id)->nullable()->change();
            $table->string($p->notification_id)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('', function (Blueprint $table) {});
    }
};
