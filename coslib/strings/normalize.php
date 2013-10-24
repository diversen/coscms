<?php

class strings_normalize {
    function newlinesToUnix($s) {
        // Normalize line endings
        // Convert all line-endings to UNIX format
        $s = str_replace("\r\n", "\n", $s);
        $s = str_replace("\r", "\n", $s);
        // Don't allow out-of-control blank lines
        //$s = preg_replace("/\n{2,}/", "\n\n", $s);
        return $s;
    }
}
