<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTipRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tip_record', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('shop_id')->unsigned()->comment('茶水费所属店铺ID（群ID）')->index('shop_id', 'idx_shop_id');
            $table->integer('transfer_id')->unsigned()->comment('茶水费红包ID')->index('transfer_id', 'idx_transfer_id');
            $table->integer('user_id')->unsigned()->comment('缴纳茶水费用户')->index('user_id', 'idx_user_id');
            $table->decimal('amount', 10, 1)->comment('茶水费金额')->default(0);
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
        Schema::dropIfExists('tip_record');
    }
}
