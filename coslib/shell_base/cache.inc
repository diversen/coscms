<?php

/**
 * File containing documentation functions for shell mode
 *
 * @package     shell
 */



function cache_clear_db ($options = null) {
    $res = dbQ::setDelete('system_cache')->filter('1 =', 1)->exec();
    if ($res) return 0;
    return 1;
}

function cache_clear_assets ($options = null) {
    $path = _COS_PATH . "/htdocs/files/cached_assets/*";
    $command = "rm -rf $path";
    cos_exec($command);
}


mainCli::setCommand('cache', array(
    'description' => 'Commands for clearing caches. ',
));



mainCli::setOption('cache_clear_db', array(
    'long_name'   => '--db-clear',
    'description' => 'Will clear db cache',
    'action'      => 'StoreTrue'
));

mainCli::setOption('cache_clear_assets', array(
    'long_name'   => '--assets-clear',
    'description' => 'Will clear cached assets',
    'action'      => 'StoreTrue'
));

