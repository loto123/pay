<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('pay_card_id')->comment('结算卡id')->nullable();
            $table->string('pay_password')->comment('支付密码')->nullable();
            $table->tinyInteger('identify_status')->comment('实名认证 1：已认证，0：未认证')->default(0);
            $table->char('id_number','18')->comment('身份证号')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('pay_card_id');
            $table->dropColumn('pay_password');
            $table->dropColumn('identity_status');
            $table->dropColumn('id_number');
        });
    }
}
