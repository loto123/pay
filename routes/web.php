<?php
use App\Pay\Model\Channel;
use App\Pay\Model\Deposit;
use App\Pay\Model\PayMethod;

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

//支付通知接收
Route::match(['get', 'post'], '/{type}-notify/channel{channel}/', function ($type, Channel $channel) {
    return $channel->acceptNotify($type);
})->name('pay_notify')->where(['type' => 'withdraw|deposit', 'channel' => '[0-9]+']);;

//支付跳回地址
Route::match(['get', 'post'], '/pay-result/method{method}', function (PayMethod $method) {
    $result = $method->getImplInstance()->displayReturn();
    $result['state'] = Deposit::getStateText($result['state']);
    return view('pay_result', $result);
})->name('pay_result');

//Auth::routes();
//
Route::get('/home', 'HomeController@index')->name('home');
