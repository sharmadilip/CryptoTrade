<?php

use Illuminate\Support\Facades\Route;

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

Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
]);

Route::get('/home', 'App\Http\Controllers\HomeController@index')->name('home')->middleware('auth');

Route::group(['middleware' => 'auth'], function () {
    Route::get('/',  'App\Http\Controllers\HomeController@index');
		Route::get('coins', ['as' => 'pages.coins', 'uses' => 'App\Http\Controllers\CoinController@index']);
       Route::get('coins/add', ['as' => 'coins.add', 'uses' => 'App\Http\Controllers\CoinController@add']);
    Route::get('orderbook', ['as' => 'coins.orderbook', 'uses' => 'App\Http\Controllers\CoinController@coin_order_book']);
		Route::get('settings', ['as' => 'pages.settings', 'uses' => 'App\Http\Controllers\SettingsValue@edit']);
    Route::get('buysellpage', ['as' => 'pages.SalePage', 'uses' => 'App\Http\Controllers\CoinController@buy_or_sell_data']);
    Route::post('/change_coin_trade_price',['as' => 'change_coin_trade_price', 'uses' => 'App\Http\Controllers\HomeController@change_coin_price_command']);

});

Route::group(['middleware' => 'auth'], function () {

	Route::get('profile', ['as' => 'profile.edit', 'uses' => 'App\Http\Controllers\ProfileController@edit']);
	Route::put('profile', ['as' => 'profile.update', 'uses' => 'App\Http\Controllers\ProfileController@update']);
    Route::post('settings/update', ['as' => 'settings.update', 'uses' => 'App\Http\Controllers\SettingsValue@update']);
    Route::post('settings/add', ['as' => 'settings.addSetting', 'uses' => 'App\Http\Controllers\SettingsValue@addSetting']);
    Route::post('coins/save', ['as' => 'coins.save', 'uses' => 'App\Http\Controllers\CoinController@save']);
	Route::put('profile/password', ['as' => 'profile.password', 'uses' => 'App\Http\Controllers\ProfileController@password']);
});
Route::get('/binance_tiker_data',['as' => 'tiker.chart', 'uses' => 'App\Http\Controllers\HomeController@binance_tiker_full_data']);
Route::get('/binance_single_tic_data',['as' => 'tiker.chart_single', 'uses' => 'App\Http\Controllers\HomeController@binance_single_tic_data']);
Route::post('/buy_form_value_on_chnage',['as' => 'coins.buy_sell_form_data', 'uses' => 'App\Http\Controllers\CoinController@buy_form_value_on_chnage']);
Route::post('/buy_sell_rate',['as' => 'coins.buy_sell_rate', 'uses' => 'App\Http\Controllers\CoinController@get_buy_or_sell']);
Route::post('/buy_coin_form',['as' => 'coins.buy_coins', 'uses' => 'App\Http\Controllers\HomeController@create_buy_order_bitbns_form']);
Route::post('/sell_coin_form',['as' => 'coins.sell_coins', 'uses' => 'App\Http\Controllers\HomeController@create_sell_order_bitbns_form']);
Route::post('/clear_all_current_bids',['as' => 'coins.clear_all_bids', 'uses' => 'App\Http\Controllers\CoinController@clear_all_current_bids']);
Route::get('/main_thread_alog',['as' => 'home.bots_alog', 'uses' => 'App\Http\Controllers\HomeController@main_thread_alog']);
Route::get('/main_thread_btc',['as' => 'home.bots_alog_btc', 'uses' => 'App\Http\Controllers\HomeController@main_thread_binance_btc_two_minutes']);
Route::get('/sys_all_data_in_one',['as' => 'home.sys_all_data', 'uses' => 'App\Http\Controllers\HomeController@sys_all_data_in_one']);
Route::post('/test_block_for_every_hook','App\Http\Controllers\HomeController@test_block_for_every_hook');
