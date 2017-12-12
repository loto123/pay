<?php

namespace App\Providers;

use Encore\Admin\Config\Config;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Config::load();

        //容器多态映射
        Relation::morphMap([
            'master' => 'App\Pay\Model\MasterContainer', //主容器
            'settle' => 'App\Pay\Model\SettleContainer', //结算容器
        ]);

        //降低隔离级别,不需要重复读
        DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
