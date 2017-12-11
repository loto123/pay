<?php

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
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::match(['get', 'post'], '/pay-notify/channel{channel_id}', function (Request $request, \App\Pay\Model\Channel $channel) {
    //支付通知接收
    return $channel->acceptNotify($request);
})->name('pay_notify');

//Auth::routes();
//
Route::get('/home', 'HomeController@index')->name('home');
