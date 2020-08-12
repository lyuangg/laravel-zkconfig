<?php

namespace Yuancode\ZkConfig;

use Illuminate\Contracts\Container\Container;

class Zk
{
    /**
     *  'host' => '127.0.0.1:3000,127.0.0.1:3001,127.0.0.1:3002',
     *  'recv_timeout' => 10000,
     *  'path' => '/test/path',
     *  'cache_path' => 'storage/zkconfig/config.json',
     *  'mode' => 'env|config',
     *  'val_type' => 'key|json',
     * ]
     *
     * @var array
     */
    protected $options;

    protected $app;
    protected $zk;
    protected $watchList = [];


    const VALUE_TYPE_KEY  = 'key';
    const VALUE_TYPE_JSON = 'json';


    public function __construct(Container $app, Array $options = [])
    {
        $this->app     = $app;
        $this->options = $options;

        $this->initOptions();
    }

    /**
     * init config
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function initOptions()
    {
        if (empty($this->options)) {
            try {
                $laravelConfig = $this->app->make('config');
                if($laravelConfig) {
                    $this->options = $laravelConfig->get('zkconfig', []);
                }
            } catch (\Exception $e) { }
        }
        $this->options['host']         = $this->options['host'] ?? '';
        $this->options['recv_timeout'] = $this->options['recv_timeout'] ?? 10000;
        $this->options['path']         = $this->options['path'] ?? '';
        $this->options['cache_path']   = $this->options['cache_path'] ??  storage_path('zkconfig.json');
        $this->options['mode']         = $this->options['mode'] ?? Config::MODE_CONFIG;
        $this->options['val_type']     = $this->options['val_type'] ?? static::VALUE_TYPE_JSON;
    }

    /**
     * set zookeeper host
     * @param $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->options['host'] = $host ?? $this->options['host'];
        return $this;
    }

    /**
     * set zookeeper timeout
     * @param $time
     * @return $this
     */
    public function setRecvTimeout($time)
    {
        $this->options['recv_timeout'] = $time ?? $this->options['recv_timeout'];
        return $this;
    }

    /**
     * set zookeeper root path
     * @param $path
     * @return $this
     */
    public function setPath($path)
    {
        $this->options['path'] = $path ?? $this->options['path'];
        return $this;
    }

    /**
     * set config replace object: env file or config file
     * @param $mode 'env/config'
     * @return $this
     */
    public function setMode($mode)
    {
        $this->options['mode'] = $mode ?? $this->options['mode'];
        return $this;
    }

    /**
     * set zookeeper values type: key/value or json
     * @param $type 'key|json'
     * @return $this
     */
    public function setValType($type)
    {
        $this->options['val_type'] = $type ?? $this->options['val_type'];
        return $this;
    }

    /**
     * set config cache path, default: storage/zkconfig.json
     * @param $path
     * @return $this
     */
    public function setCachePath($path)
    {
        $this->options['cache_path'] = $path ?? $this->options['cache_path'];
        return $this;
    }

    /**
     * set options
     * @param array $options
     * @return $this
     */
    public function setOptions(Array $options)
    {
        $this->options = $options;
        $this->initOptions();
        return $this;
    }

    public function getOptions($key = '')
    {
        if ($key) {
            return $this->options[$key];
        }
        return $this->options;
    }

    /**
     * connect zookeeper and get object
     * @return mixed
     */
    public function getZk()
    {
        if (empty($this->zk)) {
            $countHost  = count(explode(',', $this->options['host']));
            $retryCount = $countHost > 1 ? $countHost * 2 : $countHost;
            while ($retryCount > 0) {
                if ($this->retryConnect()) {
                    break;
                }
                $retryCount--;
            }
            if (empty($this->zk)) {
                throw new \ZookeeperConnectionException();
            }
        }
        return $this->zk;
    }

    /**
     * retry connect zookeeper
     * @return bool
     */
    public function retryConnect()
    {
        $host         = $this->options['host'];
        $path         = rtrim($this->options['path'], '/');
        $recv_timeout = $this->options['recv_timeout'];

        try {
            $this->zk = new \Zookeeper($host, null, $recv_timeout);
            if ($this->zk->exists($path)) {
                return true;
            }
        } catch (\ZookeeperConnectionException $e) {
            return false;
        }
        throw new \ZookeeperNoNodeException("path($path) not exists");
    }

    /**
     * watch zookeeper nodes
     */
    public function run()
    {
        $rootPath = rtrim($this->options['path'], '/');

        if ($this->options['val_type'] == static::VALUE_TYPE_JSON) {
            $this->watch(\Zookeeper::CHANGED_EVENT, 0, $rootPath);
        } else {
            $this->watch(\Zookeeper::CHILD_EVENT, 0, $rootPath);
        }
    }

    /**
     * watch zookeeper callback
     * @param int $eventType
     * @param int $connState
     * @param string $path
     */
    public function watch($eventType = 0, $connState = 0, $path = '')
    {
        $content = '';
        echo sprintf("[%s] eventType: %s, connectState:%s, path: %s \n", date("Y-m-d H:i:s"), $eventType, $connState, $path);

        if ($eventType == \Zookeeper::CHILD_EVENT) {
            $content = $this->getChildNodes($path, true);
            if (rtrim($this->options['path'], '/') != $path) {
                $content = '';
            }
        } else if ($eventType == \Zookeeper::CHANGED_EVENT) {
            $content = $this->getNodeValue($path, true);
            if (rtrim($this->options['path'], '/') != $path) {
                $content = '';
            }
        } else if ($eventType == \Zookeeper::DELETED_EVENT) {
            if (isset($this->watchList[$path])) {
                unset($this->watchList[$path]);
            }
        }
        $this->cacheConfig($content);
    }

    /**
     * read config from zookeeper
     * @return Config
     */
    public function getConfigFromZk()
    {
        $content = '';
        $path    = rtrim($this->options['path']);
        if (strtolower($this->options['val_type']) == static::VALUE_TYPE_JSON) {
            $content = $this->getNodeValue($path);
        } else {
            $content = $this->getChildNodes($path);
        }
        return new Config($this->app, $content);
    }

    /**
     * get zookeeper path value
     * @param $path
     * @param bool $isWatch
     * @return mixed
     */
    public function getNodeValue($path, $isWatch = false)
    {
        if ($isWatch) {
            return $this->getZk()->get($path, [$this, 'watch']);
        }
        return $this->getZk()->get($path);
    }

    /**
     * get zookeeper path child nodes
     * @param $path
     * @param bool $isWatch
     * @return array
     */
    public function getChildNodes($path, $isWatch = false)
    {
        $arr = [];
        if ($isWatch) {
            $childrenNodes = $this->getZk()->getchildren($path, [$this, 'watch']);
        } else {
            $childrenNodes = $this->getZk()->getchildren($path);
        }
        foreach ($childrenNodes as $node) {
            $nodePath = $path . '/' . $node;
            if (!isset($this->watchList[$nodePath])) {
                $this->watchList[$nodePath] = true;
                $arr[$node]                 = $this->getNodeValue($nodePath);
            } else {
                $arr[$node] = $this->getNodeValue($nodePath, $isWatch);
            }
        }
        return $arr;
    }

    /**
     * cache the config
     * @param null $content
     * @return Config
     */
    public function cacheConfig($content = null)
    {
        if (empty($content)) {
            $config = $this->getConfigFromZk();
        } else {
            $config = new Config($this->app, $content);
        }
        $config->save($this->options['cache_path']);
        return $config;
    }

    /**
     * get config from cache
     * @return Config|null
     */
    public function getCacheConfig()
    {
        $config = null;
        if (file_exists($this->options['cache_path'])) {
            $content = @file_get_contents($this->options['cache_path']);
            $config  = new Config($this->app, $content);
        }
        return $config;
    }

    /**
     * delete cache file
     * @return bool
     */
    public function cleanCache()
    {
        if (file_exists($this->options['cache_path'])) {
            return unlink($this->options['cache_path']);
        }
        return false;
    }

    /**
     * replace the laravel config values
     */
    public function loadZkConfig()
    {
        $config = $this->getCacheConfig();
        if (empty($config)) {
            $config = $this->cacheConfig();
        }
        if ($config) {
            $config->replaceLaravelConfig($this->options['mode']);
        }
    }
}
