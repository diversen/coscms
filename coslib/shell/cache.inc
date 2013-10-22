<?php

/**
 * File containing documentation functions for shell mode
 *
 * @package     shell
 */



function cache_clear_db ($options = null) {
    return cache_clear::db();
}

function cache_clear_assets ($options = null) {
    return cache_clear::assets();
}

function cache_clear_all ($options = null) {
    return cache_clear::all();
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

