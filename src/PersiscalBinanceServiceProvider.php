<?php

namespace Persiscal\Binance;

use Illuminate\Support\ServiceProvider;

class PersiscalBinanceServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishing();
        $this->registerCommands();
        $this->registerRoutes();
    }

    /**
     * Register the package routes.
     *
     * @return void
     */
    private function registerRoutes()
    {
        include __DIR__.'/routes/web.php';

    }


    /**
     * Register the package's publishable resources.
     *
     * @return void
     */
    private function registerPublishing()
    {
        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/config/binance-api.php' => config_path('binance-api.php'),
            ], 'persiscal-binance-config');

            $this->publishes([
                __DIR__.'/PersiscalBinanceServiceProvider.php' => app_path('Providers/PersiscalBinanceServiceProvider.php'),
            ], 'persiscal-binance-provider');

        }
    }

    /**
     * Register the package's commands.
     *
     * @return void
     */
    protected function registerCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\BinanceTestCommand::class,
                Console\BinanceTestOrderCommand::class,
            ]);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/binance-api.php', 'binance-api'
        );

    }

}
