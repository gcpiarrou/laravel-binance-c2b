<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Binance authentication
    |--------------------------------------------------------------------------
    |
    | Authentication key and secret for Binance API.
    |
     */

    'auth' => [
        'key'        => env('BINANCE_KEY', ''),
        'secret'     => env('BINANCE_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | API URLs
    |--------------------------------------------------------------------------
    |
    | Binance API endpoints
    |
     */

    'urls' => [
        'api'                   => 'https://bpay.binanceapi.com/',
        'successRouteName'      => 'binance-successUrl',
        'cancelRouteName'       => 'binance-cancelUrl',
        'webhookRouteName'      => 'binance-webhookUrl',
    ],

];