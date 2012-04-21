<?php

//////////////////////////////////////////////////////
//
// http://xszhuchao.blogbus.com/logs/130081187.html
// I Refer above article.
// Fix Some Exception.
// Make a Function like pathinfo.
// This is useful for multi byte characters.
// There some example on the bottom。
// I Use 繁體中文
// My Blog
// http://samwphp.blogspot.com/2012/04/pathinfo-function.html
//////////////////////////////////////////////////////
    function pathinfo_utf8($path)
    {

        $dirname = '';
        $basename = '';
        $extension = '';
        $filename = '';

        $pos = strrpos($path, '/');

        if($pos !== false) {
            $dirname = substr($path, 0, strrpos($path, '/'));
            $basename = substr($path, strrpos($path, '/') + 1);
        } else {
            $basename = $path;
        }

        $ext = strrchr($path, '.');
        if($ext !== false) {
            $extension = substr($ext, 1);
        }

        $filename = $basename;
        $pos = strrpos($basename, '.');
        if($pos !== false) {
            $filename = substr($basename, 0, $pos);
        }

        return array (
            'dirname' => $dirname,
            'basename' => $basename,
            'extension' => $extension,
            'filename' => $filename
        );
    } 