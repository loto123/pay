<?php

namespace App\Providers;

use App\Pay\Model\MasterContainer;
use App\Pay\Model\SettleContainer;
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
            'master' => MasterContainer::class, //主容器
            'settle' => SettleContainer::class, //结算容器
        ]);

        //降低隔离级别
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
