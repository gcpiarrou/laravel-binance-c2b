<?php

namespace Persiscal\Binance;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

use Traits\HandlesResponseErrors;

class Binance
{
    use HandlesResponseErrors;

    protected $api_key;             // API key
    protected $api_secret;          // API secret
    protected $api_url;             // API base URL

    protected $successUrlRouteName;
    protected $cancelUrlRouteName;
    protected $webhookRouteName;

    /**
     * Constructor for Binance.
     *
     * @param string $api_key       API key
     * @param string $api_secret    API secret
     * @param string $api_url       API base URL (see config for example)
     */
    public function __construct($api_key = null, $api_secret = null, $api_url = null, $successUrlRouteName = null, $cancelUrlRouteName = null, $webhookRouteName = null)
    {
        $this->api_key              = (!empty($api_key))                ? $api_key              : config('binance-api.auth.key');
        $this->api_secret           = (!empty($api_secret))             ? $api_secret           : config('binance-api.auth.secret');
        $this->api_url              = (!empty($api_url))                ? $api_url              : config('binance-api.urls.api');
        $this->successUrlRouteName  = (!empty($successUrlRouteName))    ? $successUrlRouteName  : config('binance-api.urls.successRouteName');
        $this->cancelUrlRouteName   = (!empty($cancelUrlRouteName))     ? $cancelUrlRouteName   : config('binance-api.urls.cancelRouteName');
        $this->webhookRouteName     = (!empty($webhookRouteName))       ? $webhookRouteName     : config('binance-api.urls.webhookRouteName');

    }

    /**
     * Make api requests
     *
     * @param string $url    URL Endpoint
     * @param array  $params Required or optional parameters.
     * 
     * @return mixed
     */
    private function makeRequest($url, $params = [])
    {
        $url = $this->api_url.$url;

        return $this->sendApiRequest($url, $params);
    }

    /**
     * Send request to Binance API for Private Requests.
     *
     * @param string    $url    URL Endpoint
     * @param array     $body   Request parameters
     *
     * @throws \Exception
     *
     * @return mixed
     */
    private function sendApiRequest($url, $body)
    {

        $headers = $this->generateRequestHeaders($body);
        
        try{
            $response = Http::withHeaders($headers)->post($url, $body);
        }catch(ConnectionException $e){
            return $this->hostNotFoundError($e);
        }catch(\Exception $e){
            return $this->curlError($e);
        }

        if ($response->ok()){
            return $response->collect();
        }else{
            return $this->handleError($response);
        }
    }

    /**
     * Generates a random string with 32 bytes, e.g. random ascii decimal within a-z and A-Z and loop 32 times to form a random string.
     *
     * @return string
     */
    private function generateNonce(){
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $nonce = '';
        for($i=1; $i <= 32; $i++)
        {
            $pos = mt_rand(0, strlen($chars) - 1);
            $char = $chars[$pos];
            $nonce .= $char;
        }
        return $nonce;
    }

    /**
     * Send request to Binance API for Private Requests.
     *
     * @param array     $body   Request parameters
     *
     * @throws \Exception
     *
     * @link https://developers.binance.com/docs/binance-pay/api-common#request-header
     * @return mixed
     */
    private function generateRequestHeaders($body){

        $nonce      = $this->generateNonce();
        $timestamp  = Carbon::now()->getTimestampMs();
        $jsonBody   = json_encode($body);
        $payload    = $timestamp."\n".$nonce."\n".$jsonBody."\n";

        $signature  = strtoupper(hash_hmac('SHA512', $payload, $this->api_secret));

        $headers = array();
        $headers['Content-Type']                = "application/json";
        $headers['BinancePay-Nonce']            = $nonce;
        $headers['BinancePay-Certificate-SN']   = $this->api_key;
        $headers['BinancePay-Signature']        = $signature;
        $headers['BinancePay-Timestamp']        = $timestamp;

        return $headers;
    }

    /**
     * Create order API Version 2 used for merchant/partner to initiate acquiring order.
     *
     * @param string    $merchantTradeNo    The order id, Unique identifier for the request letter or digit, no other symbol allowed, maximum length 32
     * @param decimal   $orderAmount        Minimum amount: 0.01
     * @param string    $currency           Order currency in upper case. only "BUSD","USDT","MBOX" can be accepted, fiat NOT supported.
     * @param string    $goodsType          the type of the goods for the order: "01": Tangible Goods, "02": Virtual Goods
     * @param string    $goodsCategory      Refer to the documentation
     * @param string    $referenceGoodsId   The unique ID to identify the goods.
     * @param string    $goodsName          Goods name. Special character is prohibited Example: \ " or emoji
     * 
     * @link https://developers.binance.com/docs/binance-pay/api-order-create-v2
     * @return mixed
     */
    public function createOrder($merchantTradeNo, $orderAmount, $currency, $goodsType, $goodsCategory, $referenceGoodsId, $goodsName)
    {
        $this->api_url = config('binance-api.urls.api');
        $params = ['merchantTradeNo'    =>  $merchantTradeNo];

        $data = [
            'env' => [ 'terminalType' => "WEB"],
            'merchantTradeNo'   => $merchantTradeNo,
            'orderAmount'       => $orderAmount,
            'currency'          => $currency,
            'goods' => [ 
                'goodsType'         => $goodsType,
                'goodsCategory'     => $goodsCategory,
                'referenceGoodsId'  => $referenceGoodsId,
                'goodsName'         => $goodsName
            ],
            'returnUrl'     => $this->makeUrlFromRoutes($this->successUrlRouteName, $params),
            'cancelUrl'     => $this->makeUrlFromRoutes($this->cancelUrlRouteName, $params),
            'webhookUrl'    => $this->makeUrlFromRoutes($this->webhookRouteName)
        ];

        return $this->makeRequest('binancepay/openapi/v2/order', $data, 'POST');
    }

    /**
     * Creates the returnUrl, cancelUrl and webhookUrl used in createOrder checking if the route exists.
     *
     * @param string    $routeName  Name of the route
     * @param array     $params     Route parameters
     * 
     * @return string
     */
    public function makeUrlFromRoutes($routeName = null, $params = null){
        if(Route::has($routeName)){
            return route($routeName,  $params);
        }
        return null;
    }

    /**
     * Checks if the order was successfully created.
     *
     * @param collection    $orderResponse    The order response received from Binance
     * 
     * @return bool
     */
    public function orderWasCreated($orderResponse){
        try{
            if(!$orderResponse)  return false;
            if ($orderResponse instanceof \Illuminate\Support\Collection) {
                $orderResponse = $orderResponse->toArray();
            }else if(!is_array($orderResponse)){
                return false;
            }
            if(array_key_exists('data', $orderResponse)){
                return (array_key_exists('prepayId', $orderResponse['data']) && array_key_exists('checkoutUrl', $orderResponse['data']));
            }
            return false;
        }catch(\Exception $e){
            report($e);
            return false;
        }
    }

    /**
     * Query order API used for merchant/partner to query order status.
     *
     * @param string $merchantTradeNo   The order id, Unique identifier for the request letter or digit, no other symbol allowed, maximum length 32
     * @param string $prepayId          Binance unique order id
     *
     * @link https://developers.binance.com/docs/binance-pay/api-order-query-v2
     * @return mixed
     */
    public function queryOrder($merchantTradeNo = null, $prepayId = null)
    {
        $this->api_url = config('binance-api.urls.api');

        $data = [
            "merchantTradeNo"   => $merchantTradeNo,
            'prepayId'          => $prepayId
        ];

        return $this->makeRequest('binancepay/openapi/v2/order/query', $data, 'POST');
    }

    /**
     * Close order API used for merchant/partner to close order without any prior payment activities triggered by user. The successful close result will be notified asynchronously through Order Notification Webhook with bizStatus = "PAY_CLOSED".
     *
     * @param string $merchantTradeNo   The order id, Unique identifier for the request
     * @param string $prepayId          Binance unique order id
     *
     * @link https://developers.binance.com/docs/binance-pay/api-order-close
     * @return mixed
     */
    public function closeOrder($merchantTradeNo = null, $prepayId = null)
    {
        $this->api_url = config('binance-api.urls.api');

        $data = [
            "merchantTradeNo"   => $merchantTradeNo,
            'prepayId'          => $prepayId
        ];

        return $this->makeRequest('binancepay/openapi/order/close', $data, 'POST');
    }

    /**
     * Check if an existing order was successfully closed.
     *
     * @param Collection $orderResponse Order response from closeOrder request
     *
     * @return boolean
     */
    public function orderWasClosed($orderResponse){
        try{
            if(!$orderResponse)  return false;
            if ($orderResponse instanceof \Illuminate\Support\Collection) {
                $orderResponse = $orderResponse->toArray();
            }else if(!is_array($orderResponse)){
                return false;
            }
            if(array_key_exists('data', $orderResponse)){
                return $orderResponse['data'];
            }
            return false;
        }catch(\Exception $e){
            report($e);
            return false;
        }
    }

    /**
     * Refund order API used for Merchant/Partner to refund for a successful payment.
     *
     * @param string $prepayId          Binance unique order id
     * @param string $refundAmount      You can perform multiple partial refunds, but their sum should not exceed the order amount.
     *
     * @link https://developers.binance.com/docs/binance-pay/api-order-refund
     * @return mixed
     */
    public function refundOrder($prepayId, $refundAmount)
    {
        $this->api_url = config('binance-api.urls.api');

        // The unique ID assigned by the merchant to identify a refund request.The value must be same for one refund request.
        $refundId = 'refund-payment-'.$prepayId.'-on-'.Carbon::now()->format('Y-m-d_H:i:s');
        $refundRequestId = substr($refundId, 0, 64);

        $data = [
            "refundRequestId"   => $refundRequestId,
            "prepayId"          => $prepayId,
            "refundAmount"      => $refundAmount,
        ];

        return $this->makeRequest('binancepay/openapi/order/refund', $data, 'POST');
    }

    /**
     * Check if an existing order was successfully refunded.
     *
     * @param Collection $orderResponse Order response from refundOrder request
     *
     * @return boolean
     */
    public function orderWasRefunded($orderRefund){
        try{
            if(!$orderRefund)  return false;
            if ($orderRefund instanceof \Illuminate\Support\Collection) {
                $orderRefund = $orderRefund->toArray();
            }else if(!is_array($orderRefund)){
                return false;
            }
            if(array_key_exists('status', $orderRefund)){
                return $orderRefund['status'] == 'SUCCESS';
            }
            return false;
        }catch(\Exception $e){
            report($e);
            return false;
        }
    }

    /**
     * API used for merchant/partner to query refunded order.
     *
     * @param string $refundRequestId          The unique ID assigned by the merchant to identify a refund request.

     *
     * @link https://developers.binance.com/docs/binance-pay/api-order-refund-query
     * @return mixed
     */
    public function queryRefundOrder($refundRequestId)
    {
        $this->api_url = config('binance-api.urls.api');

        $data = [
            "refundRequestId"   => $refundRequestId,
        ];

        return $this->makeRequest('binancepay/openapi/order/refund/query', $data, 'POST');
    }
    
    /**
     * API used to query one or more wallet balance.
     *
     * @param string $wallet          Binance wallet to query, currently supported enum values: FUNDING_WALLET, SPOT_WALLET
     * @param string $currency        Currency to query, for e.g, "BUSD". If no currency was sent, return all assets.
     *
     * @link https://developers.binance.com/docs/binance-pay/api-balance-query-v2
     * @return mixed
     */
    public function getBalance($wallet, $currency = null){
        $this->api_url = config('binance-api.urls.api');

        $data = [
            "wallet"    => $wallet,
            "currency"  => $currency
        ];

        return $this->makeRequest('binancepay/openapi/v2/balance', $data, 'POST');

    }

    /**
     * Proccesses the checkout returnUrl request.
     *
     * @param string $merchantTradeNo  The order id, Unique identifier for the request
     *
     */
    public function successPayment($merchantTradeNo){
        // Do something
    }

    /**
     * Proccesses the checkout cancelUrl request.
     *
     * @param string $merchantTradeNo  The order id, Unique identifier for the request
     *
     */
    public function cancelPayment($merchantTradeNo){
        // Do something
    }

    /**
     * Proccesses the Binance webhook requests.
     *
     * @param Illuminate\Http\Request $request  Request from Binance API
     *
     */
    public function webhook(Request $request){
        // Do something
    }


}
