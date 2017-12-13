<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecordIdToTipRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tip_record', function (Blueprint $table) {
            $table->unsignedInteger('record_id')->comment('所属交易记录ID')->index('record_id','idx_record_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tip_record', function (Blueprint $table) {
            $table->dropIndex('idx_record_id');
            $table->dropColumn('record_id');
        });
    }
}
