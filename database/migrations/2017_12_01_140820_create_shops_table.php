<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShopsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name");
            $table->unsignedTinyInteger("status")->default(0)->comment('店铺状态 0=正常 1=已解散 2=已冻结');
            $table->unsignedTinyInteger("type")->default(0)->comment('收费类型 0=大赢家 1=小赢家');
            $table->decimal("type_value", 10)->default(0)->comment("收费数 大赢家固定费用 小赢家固定百分比");
            $table->decimal("fee", 10,1)->default(0)->comment('手续费');
            $table->decimal("price", 10)->default(0)->comment('默认单价');
//            $table->decimal("percent", 5)->defalut(0)->comment("收费比例百分比(%)");
            $table->unsignedInteger('manager_id')->index()->comment("群主id");
            $table->boolean("use_link")->default(1)->comment("是否开启邀请链接");
            $table->boolean("active")->default(1)->comment("是否开启交易");
            $table->unsignedInteger('container_id');
            $table->unsignedSmallInteger('channel_id')->nullable()->default(0);
            $table->string('logo');
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
        Schema::dropIfExists('shops');
    }
}
