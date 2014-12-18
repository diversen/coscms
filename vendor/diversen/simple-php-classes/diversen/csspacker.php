<?php

namespace diversen;

// Found on: 
// http://code.google.com/p/css-packer-function-php/source/browse/css-packer-function-php-1.0.php

class csspacker {

    public static function packcss($s) {


    /*
     * css-packer-function-php
     *
     * Copyright (C)
     *
     * This program is free software; you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation; either version 3 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License
     * along with this program.  If not, see <http://www.gnu.org/licenses/>.
     *
     */

        $s = str_replace(array("\n", "\r", "\t", "\v", "\0", "\x0B"), '', preg_replace("/[^\x20-\xFF]/", "", trim(@strval($s))));

        $a = array("/[\ ]+/s" => " ",
            "/\; \}/s" => "}",
            "/\;\}/s" => "}",
            "/\}\ /s" => "}",
            "/\: /s" => ":",
            "/\ \{/s" => "{",
            "/\{\ /s" => "{",
            "/\; /s" => ";",
            "/\,\ /s" => ",",
            "/\/\*(.*?)\*\//s" => "");

        foreach ($a as $k => $v) {
            $s = preg_replace($k, $v, $s);
        }

        return $s;
    }

}
