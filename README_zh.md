laravel-zkconfig
========================================= 

laravel-zkconfig 是一个 laravel 的配置管理工具. 可以把一些配置信息保存在 zookeeper 中.

## 使用方法

### 安装

```bash
omposer require 'yuancode/laravel-zkconfig'
```

### 发布配置文件

```bash
php artisan vendor:publish --provider="Yuancode\ZkConfig\ZkConfigServiceProvider"  --tag=config
```

### 添加 zk 配置

```bash
create /test/zkconfig '{"app.name": "test"}'
```

### 修改代码

修改 `bootstrap/app.php` 文件, 在 `$app` 下面增加如下代码:

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

### 可使用的命令
#### 缓存 zk

```bash
php artisan zkconfig:server cache
```

#### 清理 zk 缓存

```bash
php artisan zkconfig:server cache
```

#### 自动更新 zk 缓存

```bash
php artisan zkconfig:server start
```
