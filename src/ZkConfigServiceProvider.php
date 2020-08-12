<?php

namespace Yuancode\ZkConfig;

use Illuminate\Support\ServiceProvider;
use Yuancode\ZkConfig\Commands\ZkConfigServerCommand;

class ZkConfigServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/zkconfig.php' => config_path('zkconfig.php')
        ], 'config');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerServer();
        $this->registerCommands();
    }

    /**
     * @Desc Register Zookeeper Server
     */
    protected function registerServer()
    {
        $this->app->singleton('zkconfig', function ($app) {
            $options = $app->make('config')->get('zkconfig');
            return new Zk($app, $options);
        });
    }

    /**
     * @Desc Regiter Commands
     */
    protected function registerCommands()
    {
        $this->commands([
            ZkConfigServerCommand::class,
        ]);
    }
}
