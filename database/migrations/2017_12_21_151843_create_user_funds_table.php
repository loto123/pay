<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserFundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_funds', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->index();
            $table->unsignedTinyInteger('type')->nullable()->default(0)->comment("0=充值,1=提现,2=交易收入,3=交易支出,4=转账到店铺,5=店铺转入,6=交易手续费,7=提现手续费,8=大赢家茶水费");
            $table->unsignedTinyInteger('mode')->nullable()->default(0)->comment("0=收入,1=支出");
            $table->decimal('amount', 15)->default(0)->comment("交易金额");
            $table->decimal('balance', 15)->default(0)->comment("用户余额");
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
        Schema::dropIfExists('user_funds');
    }
}
