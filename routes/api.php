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
//Route::group([
//    'auth',
//], function (Router $router) {
//    $router->post('auth/login', 'Api\AuthController@login');
//    $router->post('auth/register', 'Api\AuthController@register');
//});

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

Route::group([
    'prefix'       => '/my',
    'namespace'    => 'Api',
], function (Router $router){
    $router->get('index','UserController@index');
    $router->post('updatePassword','UserController@updatePassword');
    $router->post('setPayPassword','UserController@setPayPassword');
    $router->post('updatePayPassword','UserController@updatePayPassword');
    $router->post('updatePayCard','UserController@updatePayCard');
    $router->get('getPayCard','UserController@getPayCard');
    $router->post('identify','UserController@identify');
    $router->get('info','UserController@info');
});

Route::group([
    'prefix'      => '/card',
    'namespace'   => 'Api',
], function(Router $router){
    $router->get('index', 'CardController@index');
    $router->post('create', 'CardController@create');
    $router->post('delete', 'CardController@delete');
    $router->get('getBanks','CardController@getBanks');
});

$api = app('Dingo\Api\Routing\Router');
//app('Dingo\Api\Exception\Handler')->register(function (\Exception $exception) {
//    return Response::make(['code' => 500, 'message' => $exception->getMessage(), 'data' => []], 500);
//});
//app('Dingo\Api\Auth\Auth')->extend('jwt', function ($app) {
//    return new Dingo\Api\Auth\Provider\JWT($app['Tymon\JWTAuth\JWTAuth']);
//});
app('api.exception')->register(function (Exception $exception) {
    $request = Illuminate\Http\Request::capture();
    return app('App\Exceptions\Handler')->render($request, $exception);
});
$api->version('v1', function ($api) {
    $api->group([
        'prefix' => 'auth',
        'namespace' => 'App\Http\Controllers\Api',
    ], function ($api) {
        $api->post('login', 'AuthController@login');
        $api->post('register', 'AuthController@register');
        $api->get("login/wechat/url", 'AuthController@wechat_login_url');
        $api->post("login/wechat", 'AuthController@wechat_login');
        $api->post("valid", 'AuthController@valid');
        $api->post("sms", 'AuthController@sms');
    });
});

$api->version('v1', ['middleware' => 'api.auth'], function ($api) {
    $api->group([
        'prefix' => 'shop',
        'namespace' => 'App\Http\Controllers\Api',
    ], function ($api) {
        $api->get('lists', 'ShopController@lists');
        $api->get('lists/mine', 'ShopController@my_lists');
        $api->get('detail/{id}', 'ShopController@detail');
        $api->post('close/{id}', 'ShopController@close');
        $api->post('quit/{id}', 'ShopController@quit');
        $api->post('update/{id}', 'ShopController@update');
        $api->post('create', 'ShopController@create');
    });
});

$api->version('v1', ['middleware' => 'api.auth'], function ($api) {
    $api->group([
        'prefix' => 'transfer',
        'namespace' => 'App\Http\Controllers\Api',
    ], function ($api) {
        $api->get('show', 'TransferController@show');
        $api->get('feerecord', 'TransferController@feeRecord');
        $api->get('record', 'TransferController@record');
        $api->post('mark', 'TransferController@mark');
        $api->post('payfee', 'TransferController@payFee');
        $api->post('notice', 'TransferController@notice');
        $api->post('withdraw', 'TransferController@withdraw');
        $api->post('trade', 'TransferController@trade');
        $api->post('validate', 'TransferController@validate');
        $api->post('create', 'TransferController@create');
        $api->post('close', 'TransferController@close');
        $api->post('cancel', 'TransferController@cancel');
    });
});

Route::group([
    'prefix'      => '/notice',
    'namespace'   => 'Api',
],function(Router $router){
    $router->get('index','NoticeController@index');
    $router->post('create', 'NoticeController@create');
    $router->post('delete', 'NoticeController@delete');
    $router->post('detail', 'NoticeController@detail');
});