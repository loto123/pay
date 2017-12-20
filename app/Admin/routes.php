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
    $router->resource('/roles', RoleController::class);
    $router->resource('/permissions', PermissionController::class);
    $router->any('/user/detail/{id}','UserController@details');

    $router->any('shop','ShopController@index');
    $router->any('/shop/detail/{shop_id?}','ShopController@details');
    $router->any('/shop/updates','ShopController@updates');

    $router->post('/excel/shop', 'ExcelController@shop');

    $router->resource('pay/platform', PayPlatformController::class);
    $router->resource('pay/deposit-method', DepositMethodController::class);
    $router->resource('pay/withdraw-method', WithdrawMethodController::class);
    $router->resource('pay/entity', BusinessEntityController::class);
    $router->resource('pay/channel', PayChannelController::class);

    $router->post('/excel/user', 'ExcelController@user');

    $router->resource('bank', BankController::class);
});

Route::group([
    'prefix'        => config('admin.route.prefix').'/data',
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->any('profit', "DataController@profit");
    $router->any('transfer', "DataController@transfer");
    $router->any('record', "DataController@record");
    $router->any('test', "DataController@test");
    $router->any('users', "DataController@users");
});

Route::group([
    'prefix'        => config('admin.route.prefix').'/agent',
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->get('relation', 'AgentController@relation');
    $router->post('relation/update', 'AgentController@relation_update');
});