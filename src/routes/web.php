<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('binance/success/{merchantTradeNo}', '\App\Helpers\Binance@successPayment')	->name('binance-successUrl');
Route::get('binance/cancel/{merchantTradeNo}', 	'\App\Helpers\Binance@cancelPayment')	->name('binance-cancelUrl');
Route::get('binance/webhook', 					'\App\Helpers\Binance@webhook')			->name('binance-webhookUrl');