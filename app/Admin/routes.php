<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('/', 'HomeController@index');
    $router->resource('users',UserController::class);
    $router->resource('versions',VersionController::class);
    $router->resource('/roles', RoleController::class);
    $router->resource('/permissions', PermissionController::class);
    $router->any('/user/detail/{id}','UserController@details');

    $router->any('shop','ShopController@index');
    $router->any('/shop/detail/{shop_id}','ShopController@details');
    $router->any('/shop/updates','ShopController@updates');
    $router->any('/shop/delete/{shop_id}','ShopController@delete');

    $router->post('/excel/shop', 'ExcelController@shop');

    $router->resource('pay/platform', PayPlatformController::class);
    $router->resource('pay/deposit-method', DepositMethodController::class);
    $router->resource('pay/withdraw-method', WithdrawMethodController::class);
    $router->resource('pay/entity', BusinessEntityController::class);
    $router->resource('pay/channel', PayChannelController::class);
    $router->resource('pay/scene', PaySceneController::class);
    $router->resource('pay/deposits', DepositController::class);
    $router->resource('pay/withdraws', WithdrawController::class);
    $router->resource('uploads', UploadFileController::class);

    //代理vip卡模块
    $router->resource('agent/card-type', AgentCardTypeController::class);
    $router->resource('agent/promoter-grant', PromoterGrantController::class);

    $router->post('pay/support_banks/{platform}', 'PayPlatformController@bankSupport')->name('associate_bank');

    //支付重试
    $router->post('pay/retry-{operation}/{id}', function ($operation, $id) {
        $retry = ['charge' => new \App\Pay\Model\ChargeRetry($id), 'withdraw' => new \App\Pay\Model\WithdrawRetry($id)][$operation];
        return $retry->reDo();
    })->name('pay_retry')->where(['operation' => 'charge|withdraw', 'id' => '\d+']);


    //提现取消
    $router->post('pay/cancel-withdraw/{withdraw}', function (\App\Pay\Model\Withdraw $withdraw) {
        return ['status' => $withdraw->cancel()];
    })->name('withdraw_cancel')->where(['id' => '\d+']);

    //提现异常
    $router->get('pay/withdraw-exceptions', 'WithdrawController@exception_view');
    $router->post('/excel/user', 'ExcelController@user');

    $router->resource('bank', BankController::class);

    $router->post('/excel/data/user', 'ExcelController@dataUser');
    $router->post('/excel/data/profit', 'ExcelController@dataProfit');
});

Route::group([
    'prefix'        => config('admin.route.prefix').'/data',
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->any('profit', "DataController@profit");
    $router->any('transfer', "DataController@transfer");
    $router->any('transfer/detail/{operatorId}', "DataController@detail");
    $router->any('record', "DataController@record");
    $router->any('test', "DataController@test");
    $router->any('users', "DataController@users");
    $router->any('area', 'DataController@area');
    $router->any('areaDetail/{operatorId?}', 'DataController@areaDetail');
    $router->get('transfer/close/{id}', 'DataController@close');
});

Route::group([
    'prefix'        => config('admin.route.prefix').'/agent',
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('relation', 'AgentController@relation');
    $router->post('relation/update', 'AgentController@relation_update');
});