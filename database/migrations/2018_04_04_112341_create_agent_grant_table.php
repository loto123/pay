<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgentGrantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('agent_grant', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('grant_by')->comment('授权人id');
            $table->integer('grant_to')->comment('被授权人id');
            $table->tinyInteger('by_admin')->comment('是否后台操作,1:是，0:不是');
            $table->string('old_roles')->comment('授权前用户角色')->nullable();
            $table->string('new_roles')->comment('授权后用户角色');
            $table->timestamps();
            $table->index(['grant_by','grant_to']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('agent_grant');
    }
}
