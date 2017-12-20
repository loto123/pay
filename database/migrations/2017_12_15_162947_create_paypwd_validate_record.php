<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaypwdValidateRecord extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paypwd_validate_record', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('验证交易密码用户ID')->index('user_id', 'idx_user_id');
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
        Schema::dropIfExists('paypwd_validate_record');
    }
}
