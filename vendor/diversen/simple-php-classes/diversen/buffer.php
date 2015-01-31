<?php

namespace diversen;

class buffer {

    public static function cleanAll() {

        while (ob_get_level()) {
            ob_end_flush();
        }
    }

}
