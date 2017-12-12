<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransferUserRelationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_user_relation', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('transfer_id')->unsigned()->comment('交易ID')->index('transfer_id', 'idx_transfer_id');
            $table->integer('user_id')->unsigned()->comment('参与用户')->index('user_id', 'idx_user_id');
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
        Schema::dropIfExists('transfer_user_relation');
    }
}
