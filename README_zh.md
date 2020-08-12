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
