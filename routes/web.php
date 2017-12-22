<?php
use App\Pay\Model\Channel;
use App\Pay\Model\DepositMethod;
use App\Pay\Model\WithdrawMethod;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

//通知接收
Route::match(['get', 'post'], '/{notify_type}-notify/c{channel}_m{method}', function ($notify_type, Channel $channel) {
    $method = ['pay' => DepositMethod::class, 'withdraw' => WithdrawMethod::class][$notify_type];
    $method = new $method;
    return $method->acceptNotify($channel);
})->name('common_notify')->where(['notify_type' => 'pay|withdraw', 'channel' => '\d+', 'method' => '\d+']);

//支付跳回地址
Route::match(['get', 'post'], '/pay-result/m{method}', function (DepositMethod $method) {
    return $method->showDepositResult();
})->name('pay_result');

//Auth::routes();
//
Route::get('/home', 'HomeController@index')->name('home');
