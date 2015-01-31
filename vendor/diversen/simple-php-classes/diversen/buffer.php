<?php

namespace diversen;

class buffer {

    /**
     * clean all ob buffers
     */
    public static function cleanAll() {

        while (ob_get_level()) {
            ob_end_flush();
        }
    }

}
