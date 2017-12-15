<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMarkToTransferUserRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transfer_user_relation', function (Blueprint $table) {
            $table->tinyInteger('mark')->comment('标记 0 未标记 1 已标记')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transfer_user_relation', function (Blueprint $table) {
            $table->dropColumn('mark');
        });
    }
}
