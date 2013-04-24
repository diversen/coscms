<?php

define('_COS_PATH', realpath('.'));
include_once "coslib/coslibSetup.php";

include "vendor/Thread.php";

config::loadMainCli();
$db = new db();
$db->connect();

 
// test to see if threading is available
if( ! Thread::available() ) {
    die( 'Threads not supported' );
}
 
// define the function to be run as a separate thread
function processImage( $_image ) {
    // expensive image processing logic here
    echo "$_image" . "\n";
    ##processImage($_image);
}
 
$threads = array();
$index = 100;
 
while($index /* new DirectoryIterator( '/path/to/images' ) as $item */ ) {
    //if( $item->isFile() ) {
        $threads[$index] = new Thread( 'processImage' );
        $threads[$index]->start( md5(uniqid()) );
        --$index;
        //}
}
 
// wait for all the threads to finish
while( !empty( $threads ) ) {
    foreach( $threads as $index => $thread ) {
        if( ! $thread->isAlive() ) {
            unset( $threads[$index] );
        }
    }
    // let the CPU do its work
    sleep( 1 );
}

