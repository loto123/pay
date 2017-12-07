<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->resource('pay/platform', PayPlatformController::class);
    $router->resource('pay/method', PayMethodController::class);
    $router->resource('pay/entity', BusinessEntityController::class);
    $router->resource('pay/channel', PayChannelController::class);

});
