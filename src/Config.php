<?php

namespace Yuancode\ZkConfig;

use Illuminate\Contracts\Container\Container;

class Config
{
    protected $app;
    protected $content;
    protected $mode;

    const MODE_ENV    = 'env';
    const MODE_CONFIG = 'config';


    public function __construct(Container $app, $content)
    {
        $this->app     = $app;
        $this->content = $content;
    }

    public function replaceLaravelConfig($mode)
    {
        $method = 'replace' . ucfirst(strtolower($mode));
        if (method_exists($this, $method)) {
            $this->{$method}();
        }
    }

    public function isLumen()
    {
        $version = $this->app->version();
        if(strpos(strtolower($version), 'lumen') !== false) {
            return true;
        } else {
            return false;
        }
    }

    public function replaceEnv()
    {
        $items = $this->getConfigItems();
        if ($items) {
            foreach ($items as $key => $val) {
                if($this->isLumen()) {
                    config([$key=>$val]);
                } else {
                    $key = str_replace(['.', '-'], ['_', '_'], strtoupper($key));
                    putenv("$key=$val");
                    $_ENV[$key] = $val;
                }
            }
        }
    }

    public function replaceConfig()
    {
        $items = $this->getConfigItems();
        if ($items) {
            foreach ($items as $key => $val) {
                $key = str_replace('-', '.', strtolower($key));
                config([$key=>$val]);
            }
        }
    }

    public function getConfigItems()
    {
        if (is_string($this->content)) {
            return json_decode($this->content, true);
        }
        return $this->content;
    }

    public function save($path)
    {
        $content = $this->getConfigString();
        if (strlen($content) > 8192) {
            file_put_contents($path, $content, LOCK_EX);
        } else {
            file_put_contents($path, $content);
        }
    }

    public function getConfigString()
    {
        if (is_array($this->content)) {
            return @json_encode($this->content);
        }
        return $this->content;
    }

    public function __toString()
    {
        return $this->getConfigString();
    }
}
