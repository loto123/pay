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
        try {
            Config::load();
        } catch (\Exception $e){}

        //容器多态映射
        Relation::morphMap([
            'master' => MasterContainer::class, //主容器
            'settle' => SettleContainer::class, //结算容器
        ]);

        //降低隔离级别
        DB::statement('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');

        //开启sql记录
        if (config('app.debug')) {
            DB::connection()->enableQueryLog();
        }
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
