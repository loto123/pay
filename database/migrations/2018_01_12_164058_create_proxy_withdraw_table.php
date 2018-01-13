<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProxyWithdrawTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proxy_withdraw', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('提现用户')->index('user_id', 'idx_user_id');
            $table->decimal('amount', 11, 2)->comment('提现金额')->default(0);
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
        Schema::dropIfExists('proxy_withdraw');
    }
}
