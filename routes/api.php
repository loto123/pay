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
    'prefix'       => '/my',
    'namespace'    => 'Api',
    'middleware' => ['api.auth', 'block']
], function (Router $router){
    $router->get('index','UserController@index');
    $router->post('updatePassword','UserController@updatePassword');
    $router->post('setPayPassword','UserController@setPayPassword');
    $router->post('updatePayPassword','UserController@updatePayPassword');
    $router->post('updatePayCard','UserController@updatePayCard');
    $router->get('getPayCard','UserController@getPayCard');
    $router->post('identify','UserController@identify');
    $router->get('info','UserController@info');
    $router->get('parent','UserController@parent');
    $router->post('pay_password','UserController@pay_password');
    $router->post('resetPayPassword','UserController@resetPayPassword');
});

Route::group([
    'prefix'      => '/card',
    'namespace'   => 'Api',
    'middleware' => ['api.auth', 'block']
], function(Router $router){
    $router->get('index', 'CardController@index');
    $router->post('create', 'CardController@create');
    $router->post('delete', 'CardController@delete');
    $router->get('getBanks','CardController@getBanks');
    $router->get('getBankCardParams','CardController@getBankCardParams');
    $router->get('otherCards','CardController@otherCards');
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
        $api->post("password/reset", 'AuthController@reset_password');
    });
});
//
//$api->version('v1', function ($api) {
//    $api->group([
//        'prefix' => '/',
//        'namespace' => 'App\Http\Controllers\Api',
//    ], function ($api) {
//        $api->get('time', 'CommonController@time');
//    });
//});

$api->version('v1', function ($api) {
    $api->group([
        'prefix' => 'shop',
        'namespace' => 'App\Http\Controllers\Api',
    ], function ($api) {
        $api->get('summary/{id}', 'ShopController@shop_summary');
    });
});

$api->version('v1', ['middleware' => ['api.auth', 'block']], function ($api) {
    $api->group([
        'prefix' => 'shop',
        'namespace' => 'App\Http\Controllers\Api',
    ], function ($api) {
        $api->get('lists', 'ShopController@lists');
        $api->get('lists/mine', 'ShopController@my_lists');
        $api->get('lists/all', 'ShopController@all');
        $api->get('detail/{id}', 'ShopController@detail');
        $api->get('members/{id}', 'ShopController@members');
        $api->post('members/{shop_id}/delete/{user_id}', 'ShopController@member_delete');
        $api->post('close/{id}', 'ShopController@close');
        $api->post('quit/{id}', 'ShopController@quit');
        $api->post('update/{id}', 'ShopController@update');
        $api->post('join/{id}', 'ShopController@join');
        $api->post('create', 'ShopController@create');
        $api->get('qrcode/{id}', 'ShopController@qrcode');
        $api->get('account/{id}', 'ShopController@account');
        $api->get('messages', 'ShopController@messages');
        $api->get('messages/count', 'ShopController@messages_count');
        $api->post('agree', 'ShopController@agree');
        $api->post('ignore', 'ShopController@ignore');
        $api->get('profit', 'ShopController@profit');
        $api->get('user/search', 'ShopController@user_search');
        $api->post('invite/{shop_id}/{user_id}', 'ShopController@invite');
        $api->post('transfer/{shop_id}', 'ShopController@transfer');
        $api->post('transfer/{shop_id}/{user_id}', 'ShopController@transfer_member')->where('shop_id', '[0-9]+');
        $api->get('transfer/records/{shop_id}', 'ShopController@transfer_records')->where('shop_id', '[0-9]+');
        $api->get('transfer/records/month/{shop_id}', 'ShopController@month_data')->where('shop_id', '[0-9]+');
        $api->get('transfer/records/detail/{id}', 'ShopController@record_detail');


    });
});

$api->version('v1', ['middleware' => ['api.auth', 'block']], function ($api) {
    $api->group([
        'prefix' => 'transfer',
        'namespace' => 'App\Http\Controllers\Api',
    ], function ($api) {
        $api->get('show', 'TransferController@show');
        $api->get('feerecord', 'TransferController@feeRecord');
        $api->get('record', 'TransferController@record');
        $api->get('shop', 'TransferController@shop');
        $api->post('mark', 'TransferController@mark');
        $api->post('payfee', 'TransferController@payFee');
        $api->post('notice', 'TransferController@notice');
        $api->post('withdraw', 'TransferController@withdraw');
        $api->post('trade', 'TransferController@trade');
        $api->post('validate', 'TransferController@valid');
        $api->post('create', 'TransferController@create');
        $api->post('close', 'TransferController@close');
        $api->post('cancel', 'TransferController@cancel');
        $api->post('realget', 'TransferController@realGet');
    });
});

$api->version('v1', ['middleware' => ['api.auth', 'block']], function ($api) {
    $api->group([
        'prefix' => 'account',
        'namespace' => 'App\Http\Controllers\Api',
    ], function ($api) {
        $api->get('/', 'AccountController@index');
        $api->get('pay-methods/{os}/{scene}', 'AccountController@payMethods')->where(['os' => 'unknown|android|ios', 'scene' => '\d+']);
        $api->get('withdraw-methods', 'AccountController@withdrawMethods');
        $api->get('records', 'AccountController@records');
        $api->get('records/detail/{id}', 'AccountController@record_detail');
        $api->post('charge', 'AccountController@charge');
        $api->post('withdraw', 'AccountController@withdraw');
        $api->post('transfer', 'AccountController@transfer');
        $api->get('records/month', 'AccountController@month_data');
        $api->get('deposit_quotas', 'AccountController@depositQuotaList');
    });

});

//代理相关接口
$api->version('v1', ['middleware' => ['api.auth', 'block']], function ($api) {
    $api->group([
        'prefix' => 'agent',
        'namespace' => 'App\Http\Controllers\Api',
    ], function ($api) {
        $api->get('/bound_vip', 'AgentController@myVip');
    });

});

//推广员接口

$api->version('v1', ['middleware' => ['api.auth', 'block', 'role:promoter']], function ($api) {
    $api->group([
        'prefix' => 'promoter',
        'namespace' => 'App\Http\Controllers\Api',
    ], function ($api) {
        $api->post('/transfer-card', 'PromoterController@transferCard');
        $api->post('/bind-card', 'PromoterController@bindCard');
        $api->post('/grant', 'PromoterController@grant');
        $api->get('/cards-used', 'PromoterController@cardsUseRecords');
        $api->get('/grant-history', 'PromoterController@grantRecords');
        $api->get('/cards-reserve', 'PromoterController@cardsReserve');
        $api->get('/cards_used_num', 'PromoterController@cardsUsedNum');
        $api->post('/query-agent', 'PromoterController@queryAgent');
        $api->post('/query-promoter', 'PromoterController@queryPromoter');
        $api->post('/query-none-promoter', 'PromoterController@queryNonePromoter');
        $api->post('/card-detail', 'PromoterController@cardDetail');
    });

});

//宠物交易接口
$api->version('v1', ['middleware' => ['api.auth', 'block']], function ($api) {
    $api->group([
        'prefix' => 'pet',
        'namespace' => 'App\Http\Controllers\Api',
    ], function ($api) {
        $api->get('/sellable', 'PetTradeController@sellable');
        $api->get('/egg_acquire_times', 'PetTradeController@eggCanDrawTimes');

        $api->post('/acquire_egg', 'PetTradeController@freeEgg');
        $api->post('/brood', 'PetTradeController@broodTheEgg');
        $api->post('/on_sale', 'PetTradeController@findSellBill');
    });

});


$api->version('v1', ['middleware' => ['api.auth', 'block']], function ($api) {
    $api->group([
        'prefix' => 'proxy',
        'namespace' => 'App\Http\Controllers\Api',
    ], function ($api) {
        $api->get('share', 'ProxyController@share');
        $api->get('members/count', 'ProxyController@members_count');
        $api->get('members', 'ProxyController@members');
        $api->get('qrcode', 'ProxyController@qrcode');
        $api->post('create', 'ProxyController@create');
    });

});

$api->version('v1', ['middleware' => ['api.auth', 'block']], function ($api) {
    $api->group([
        'prefix' => 'index',
        'namespace' => 'App\Http\Controllers\Api',
    ], function ($api) {
        $api->get('/', 'IndexController@index');
    });

});

$api->version('v1', function ($api) {
    $api->group([
        'prefix' => 'app',
        'namespace' => 'App\Http\Controllers\Api',
    ], function ($api) {
        $api->get("version", 'AppController@version');
    });
});

Route::group([
    'prefix'      => '/notice',
    'namespace'   => 'Api',
    'middleware' => ['api.auth', 'block']
],function(Router $router){
    $router->get('index','NoticeController@index');
    $router->post('create', 'NoticeController@create');
    $router->post('delete', 'NoticeController@delete');
    $router->get('detail', 'NoticeController@detail');
    $router->post('operator', 'NoticeController@operator');
});

Route::group([
    'prefix' => '/profit',
    'namespace' => 'Api',
    'middleware' => ['api.auth', 'block', 'proxy']
], function (Router $router) {
    $router->get('index', 'ProfitController@index');
    $router->get('balance', 'ProfitController@balance');
    $router->post('count', 'ProfitController@count');
    $router->post('data', 'ProfitController@data');
    $router->post('withdraw', 'ProfitController@withdraw');
    $router->get('show/{id}','ProfitController@show')->where('id', '[0-9]+');
    $router->post('withdraw/count', 'ProfitController@withdrawCount');
    $router->post('withdraw/data', 'ProfitController@withdrawData');
    $router->get('withdraw/show/{id}','ProfitController@withdrawShow')->where('id', '[0-9]+');
});