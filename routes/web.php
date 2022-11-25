Route::get('binance/success/{merchantTradeNo}', '\App\Helpers\Binance@successPayment')	->name('binance-successUrl');
Route::get('binance/cancel/{merchantTradeNo}', 	'\App\Helpers\Binance@cancelPayment')	->name('binance-cancelUrl');
Route::get('binance/webhook', 					'\App\Helpers\Binance@webhook')			->name('binance-webhookUrl');