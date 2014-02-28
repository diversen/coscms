<?php

/**
 * file contains filter for creating links from urls
 * @package    filters
 */

/**
 * class contains method for creating links from urls
 * much the same as autolink class, but this is better
 * as there is a options for setting links not to be linkified
 * @package    filters
 */
class autolinkext {

    /**
     * filter method
     * @param strin $text to filter
     * @return string $text
     */
    public static function filter($text){        
       $text = self::autoLink($text);
       return $text;
    }
    
   /**
    * found on: http://stackoverflow.com/questions/1925455/how-to-mimic-stackoverflow-auto-link-behavior
    * from http://daringfireball.net/2009/11/liberal_regex_for_matching_urls
    * Replace links in text with html links
    *
    * @param  string $text
    * @return string $string
    */
   public static function autoLink($text)
   {
      $pattern  = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
      $callback = function($matches) { 
          $url       = array_shift($matches);
          $url_parts = parse_url($url);
          $deny = autolinkext::getDenyHosts();

          // check for links that we will be transformed from link
          // to inline content, e.g. youtube
          if (in_array($url_parts['host'], $deny)) {
;              return $url;
          }
          
          $text = parse_url($url, PHP_URL_HOST) . parse_url($url, PHP_URL_PATH);
          $text = preg_replace("/^www./", "", $text);

          $last = -(strlen(strrchr($text, "/"))) + 1;
          if ($last < 0) {
              $text = substr($text, 0, $last) . "&hellip;";
          }

          return sprintf('<a target="_blank" rel="nofollow" href="%s">%s</a>', $url, $text);
      };

      return preg_replace_callback($pattern, $callback, $text);
    }
    
    public static function getDenyhosts () {
        return array ('www.vimeo.com', 'soundcloud.com', 'youtu.be', 'www.youtu.be', 'www.youtube.com', 'youtube.com');
    }
}

/**
 * added for autoloading purpose
 * @package filters
 */
class filters_autolinkext extends autolinkext {}
