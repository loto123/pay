<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('所属用户')->index('user_id','idx_user_id');
            $table->string('holder_name', 30)->comment('持卡人姓名');
            $table->char('holder_id', 18)->comment('持卡人身份证号');
            $table->string('holder_mobile', 20)->comment('持卡人银行预留手机号');
            $table->string('card_num','19')->comment('银行卡号');
            $table->string('bank_id','30')->comment('所属银行');
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
        Schema::dropIfExists('user_cards');
    }
}
