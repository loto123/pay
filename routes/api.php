<?php

use Illuminate\Routing\Router;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::group([
    'auth',
], function (Router $router) {
    $router->post('auth/login', 'Api\AuthController@login');
    $router->post('auth/register', 'Api\AuthController@register');
});

Route::any('test', 'Api\TestController@index');
Route::group([
    'prefix'        => '/shop',
    'namespace'     => 'Api',
], function (Router $router) {
    $router->post('create', 'ShopController@create');
    $router->get('types', 'ShopController@types');
});

Route::group([
    'prefix'        => '/transfer',
    'namespace'     => 'Api',
], function (Router $router) {
    $router->post('create', 'TransferController@create');
});