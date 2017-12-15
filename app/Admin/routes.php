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
    $router->any('/user/detail/{id}','UserController@details');

    $router->any('shop','ShopController@index');
    $router->any('/shop/detail/{shop_id?}','ShopController@details');
    $router->any('/shop/updates','ShopController@updates');

    $router->post('/excel/shop', 'ExcelController@shop');
    $router->post('/excel/user', 'ExcelController@user');
});

Route::group([
    'prefix'        => config('admin.route.prefix').'/data',
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {
    $router->any('profit', "DataController@profit");
    $router->any('transfer', "DataController@transfer");
    $router->any('record', "DataController@record");
});