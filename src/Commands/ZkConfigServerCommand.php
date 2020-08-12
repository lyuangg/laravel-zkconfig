<?php

namespace Yuancode\ZkConfig\Commands;

use Illuminate\Console\Command;

class ZkConfigServerCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'zkconfig:server {action : start|cache|clean}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Zookeeper config';


    protected $action;

    /**
     * The configs for this package.
     *
     * @var array
     */
    protected $config;

    /**
     * Excute the console command.
     *
     */
    public function handle()
    {
        $this->runAction();
    }

    /**
     * Run action
     */
    protected function runAction()
    {
        $action = $this->argument('action');
        if (method_exists($this, $action)) {
            $this->{$action}();
        } else {
            $this->error("Invalid argument '{$action}'");
        }
    }

    /**
     * Start
     */
    protected function start()
    {
        $zk = $this->laravel->make('zkconfig');
        $zk->run();
        $this->info("Zkconfig start successfully!");
        $this->info("Please Enter 'Ctrl + C' to Stop ");
        while (true) {
            sleep(3);
        }
    }

    /**
     * Cache
     */
    protected function cache()
    {
        $zk   = $this->laravel->make('zkconfig');
        $path = $zk->getOptions('cache_path');
        $zk->cacheConfig();
        $this->info("zkconfig cache config($path) successfully!");
    }

    /**
     * Clean
     */
    protected function clean()
    {
        $zk   = $this->laravel->make('zkconfig');
        $path = $zk->getOptions('cache_path');
        if ($zk->cleanCache()) {
            $this->info("zkconfig clean cache($path) successfully!");
        } else {
            $this->error("zkconfig clean cache($path) failed");
        }
    }


}
