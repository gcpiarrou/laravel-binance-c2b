<?php

namespace Persiscal\Binance\Console;

use Illuminate\Console\Command;

class BinanceGetBalanceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'binance:get-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get binance balance of spot wallet';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $binance = new \Persiscal\Binance\Binance();
        $this->comment('Getting balance:');
        $balance = $binance->getBalance('SPOT_WALLET');
        $this->info(print_r($balance));
    }
}
