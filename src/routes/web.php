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

Route::get('binance/success/{merchantTradeNo}', '\Persiscal\Binance\Binance@successPayment')	->name(config('binance-api.urls.successRouteName'));
Route::get('binance/cancel/{merchantTradeNo}', 	'\Persiscal\Binance\Binance@cancelPayment')	    ->name(config('binance-api.urls.cancelRouteName'));
Route::get('binance/webhook', 					'\Persiscal\Binance\Binance@webhook')			->name(config('binance-api.urls.webhookRouteName'));