<?php


function hello_world_output () {
    echo "Hello world" . "\n";
}

function hello_world_something ($params) {
    echo $params['sentence'] . "\n";
}

mainCli::setCommand('hello_world', array(
    'description' => 'Command saying hello world.',
));

mainCli::setOption('hello_world_output', array(
    'long_name'   => '--say-it',
    'description' => 'Set this flag in order to really say it',
    'action'      => 'StoreTrue'
));

mainCli::setOption('hello_world_something', array(
    'long_name'   => '--say-something',
    'short_name' => '-s',
    'description' => 'Set this flag in order to really say it',
    'action'      => 'StoreTrue'
));

mainCli::setArgument(
    'sentence',
    array('description'=> 'Say something else than hello world',
        'optional' => true,
));

