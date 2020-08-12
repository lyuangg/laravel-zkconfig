<?php
return [
    'host'         => env('ZKCONFIG_HOST', '127.0.0.1:2181'),
    'recv_timeout' => env('ZKCONFIG_RECV_TIMEOUT', 10000),
    'path'         => env('ZKCONFIG_PATH'),
    'cache_path'   => env('ZKCONFIG_CACHE_PATH'),
    'mode'         => env('ZKCONFIG_MODE', 'config'),
    'val_type'     => env('ZKCONFIG_VAL_TYPE', 'json'),
];
