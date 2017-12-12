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
});
