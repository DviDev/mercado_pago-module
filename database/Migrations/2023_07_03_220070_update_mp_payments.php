<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('mp_payments', function (Blueprint $table) {
            if (DB::getDefaultConnection() == 'sqlite') {
                return;
            }
            $table->dropForeign('mp_payments_preference_id_foreign');
            $table->dropColumn('preference_id');
        });
    }

    public function down()
    {
        Schema::table('', function (Blueprint $table) {});
    }
};
