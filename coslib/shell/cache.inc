<?php

/**
 * File containing documentation functions for shell mode
 *
 * @package     shell
 */



function cache_clear_db ($options = null) {
    $res = db_q::setDelete('system_cache')->filter('1 =', 1)->exec();
    if ($res) return 0;
    return 1;
}

function cache_clear_assets ($options = null) {
    cos_needs_root();
    $path = _COS_PATH . "/htdocs/files/default/cached_assets/*";
    $command = "rm -rf $path";
    cos_exec($command);
}

function cache_clear_all ($options = null) {
    cos_needs_root();
    cache_clear_assets();
    cache_clear_db();
}


mainCli::setCommand('cache', array(
    'description' => 'Commands for clearing caches. ',
));



mainCli::setOption('cache_clear_db', array(
    'long_name'   => '--clear-db',
    'description' => 'Will clear db cache - only works on default domain',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cache_clear_assets', array(
    'long_name'   => '--clear-assets',
    'description' => 'Will clear cached assets',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cache_clear_all', array(
    'long_name'   => '--clear-all',
    'description' => 'Will clear all cached assets, and db cache',
    'action'      => 'StoreTrue'
));

