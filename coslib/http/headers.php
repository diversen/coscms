<?php

/**
 * @package http
 * 
 */

/**
 * class for doing http headers
 * @package http
 */
class http_headers {

   /**
    * parse curl headers string and return array
    * @param string $response
    * @return array $headers
    */
    public static function parseCurlHeaders($response) {
        $headers = array();

        $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

        foreach (explode("\r\n", $header_text) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list ($key, $value) = explode(': ', $line);

                $headers[$key] = $value;
            }
        }
        return $headers;
    }

    /**
     * get url headers as array
     * @param string $url
     * @return array $headers
     */
    public static function getCurlHeadersAry($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_FILETIME, true);
        curl_setopt($curl, CURLOPT_NOBODY, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $headers = curl_exec($curl);
        return self::parseCurlHeaders($headers);
    }

}
