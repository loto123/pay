<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopFundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_funds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('shop_id')->index();
            $table->unsignedTinyInteger('type')->nullable()->default(0)->comment("0=转账给个人 1=转账给个人 2=从个人转账");
            $table->unsignedTinyInteger('mode')->nullable()->default(0)->comment("0=收入,1=支出");
            $table->decimal('amount', 15)->default(0)->comment("交易金额");
            $table->decimal('balance', 15)->default(0)->comment("店铺余额");
            $table->string('no')->comment("交易单号")->nullable()->default("");
            $table->string('remark')->comment("备注")->nullable()->default("");
            $table->unsignedTinyInteger('status')->nullable()->default(0)->comment("0=正在处理,1=成功,2=失败");
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
        Schema::dropIfExists('shop_funds');
    }
}
