<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_messages', function (Blueprint $table) {
            $table->increments('id');
            $table->string("title");
            $table->timestamp("send_at")->nullable()->default(null);
            $table->string("link")->nullable()->default("");
            $table->text("content")->nullable();
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
        Schema::dropIfExists('system_messages');
    }
}
