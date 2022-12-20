<?php

namespace Persiscal\Binance\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BinanceTestOrderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'binance:test-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verbosely creates and closes an order with random merchant trade number';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $binance = new \Persiscal\Binance\Binance();
        $randomMerchantTradeNo = Str::random(32);

        $this->comment('The order ID will be: '.$randomMerchantTradeNo);
        $createOrderResponse = $binance->createOrder($randomMerchantTradeNo, 1, 'USDT', '02', 'Z000', 'api-test', 'Testing the API');
            $this->comment('--- Start of created order response ---');
            $this->info(print_r($createOrderResponse));
            $this->comment('--- End of created order response ---');

        if($orderWasCreated = $binance->orderWasCreated($createOrderResponse)){
            $this->info('The order was successfully created');
        }else{
            $this->error('An error occurred while creating the order');
        }

        if($orderWasCreated){
            $this->comment('Querying order');
            $orderQuery = $binance->queryOrder($randomMerchantTradeNo);
            $this->comment('--- Start of order query response ---');
            $this->info(print_r($orderQuery));
            $this->comment('--- End of order query response ---');

            $this->comment('Closing order');
            $closeOrderResponse = $binance->closeOrder($randomMerchantTradeNo);
            $this->comment('--- Start of close order response ---');
            $this->info(print_r($closeOrderResponse));
            $this->comment('--- End of close order response ---');

            if($binance->orderWasClosed($closeOrderResponse)){
                $this->info('The order was successfully closed');

                $this->comment('Querying closed order');
                $closedOrderQuery = $binance->queryOrder($randomMerchantTradeNo);
                $this->comment('--- Start of closed order query response ---');
                $this->info(print_r($closedOrderQuery));
                $this->comment('--- End of closed order query response ---');
                
            }else{
                $this->error('An error occurred while closing the order');
            }
        }
    }
}
