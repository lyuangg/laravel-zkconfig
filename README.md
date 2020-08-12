laravel-zkconfig
========================================

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
