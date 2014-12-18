<?php

namespace diversen;
/**
 * File contains GPS function and casses
 * @package gps 
 */

/**
 * Found on stackoverflow
 * Sligtly modified
 * http://stackoverflow.com/a/4178862/464549
 * @param type $exif
 * @param type $assoc
 * @return type 
 */
class gps {

    public static function get($exif, $assoc = false) {
        //get the EXIF
        //$exif = exif_read_data($fileName);
        //get the Hemisphere multiplier

        $LatM = 1;
        $LongM = 1;
        if ($exif["GPSLatitudeRef"] == 'S') {
            $LatM = -1;
        }
        if ($exif["GPSLongitudeRef"] == 'W') {
            $LongM = -1;
        }

        //get the GPS data
        $gps['LatDegree'] = $exif["GPSLatitude"][0];
        $gps['LatMinute'] = $exif["GPSLatitude"][1];
        $gps['LatgSeconds'] = $exif["GPSLatitude"][2];
        $gps['LongDegree'] = $exif["GPSLongitude"][0];
        $gps['LongMinute'] = $exif["GPSLongitude"][1];
        $gps['LongSeconds'] = $exif["GPSLongitude"][2];

        //convert strings to numbers
        foreach ($gps as $key => $value) {
            $pos = strpos($value, '/');
            if ($pos !== false) {
                $temp = explode('/', $value);
                $gps[$key] = $temp[0] / $temp[1];
            }
        }

        //calculate the decimal degree
        $result['latitude'] = $LatM * ($gps['LatDegree'] + ($gps['LatMinute'] / 60) + ($gps['LatgSeconds'] / 3600));
        $result['longitude'] = $LongM * ($gps['LongDegree'] + ($gps['LongMinute'] / 60) + ($gps['LongSeconds'] / 3600));

        if ($assoc) {
            return $result;
        }

        return json_encode($result);
    }
}
