laravel-zkconfig
========================================

[简体中文](./README_zh.md)

laravel-zkconfig is a laravel configuration management tool. You can save some configuration information in zookeeper.

## Instructions

### Installation

```bash
composer require 'yuancode/laravel-zkconfig'
```

### Publish configuration files

```bash
php artisan vendor:publish --provider="Yuancode\ZkConfig\ZkConfigServiceProvider" --tag=config
```

### Add zk configuration

```bash
create /test/zkconfig '{"app.name": "test"}'
```

### Modify the code

Modify the `bootstrap/app.php` file and add the following code under `$app`:

```php
$app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) {
     $zk = new \Yuancode\ZkConfig\Zk($app);
     $zk->loadZkConfig();
});
```
#### lumen

```php
$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$zk = new \Yuancode\ZkConfig\Zk($app);
$zk->setHost('127.0.0.1:2181')    //zookeeper host
->setPath('/app/zkconfig/')       //config root path
->setCachePath(storage_path('zkconfig/config.json')) //config cache path
->setMode(\Yuancode\ZkConfig\Config::MODE_ENV)  //replace env variable
->setValType(\Yuancode\ZkConfig\Zk::VALUE_TYPE_KEY) //key,value mode
->loadZkConfig();  //load config
```


### Available commands
#### Cache zk

```bash
php artisan zkconfig:server cache
```

#### Clean up the zk cache

```bash
php artisan zkconfig:server cache
```

#### Automatically update the zk cache

```bash
php artisan zkconfig:server start
```
