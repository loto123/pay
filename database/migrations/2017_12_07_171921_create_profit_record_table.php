<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfitRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('profit_record', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('record_id')->comment('所属交易记录ID')->index('record_id','idx_record_id');
            $table->integer('user_id')->unsigned()->comment('交易用户ID')->index('user_id', 'idx_user_id');
            $table->smallInteger('fee_percent')->comment('公司手续费比例')->default(0);
            $table->decimal('fee_amount',11, 2)->comment('公司手续费金额')->default(0);
            $table->unsignedInteger('proxy')->comment('分润代理ID')->index('proxy','idx_proxy');
            $table->smallInteger('proxy_percent')->comment('代理分润比例')->default(0);
            $table->decimal('proxy_amount',11, 2)->comment('代理分润金额')->default(0);
            $table->unsignedInteger('operator')->comment('分润运营ID')->index('operator','idx_operator');
//            $table->smallInteger('operator_percent')->comment('运营分润比例')->default(0);
//            $table->decimal('operator_amount',11, 2)->comment('运营分润费金额')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('profit_record');
    }
}
