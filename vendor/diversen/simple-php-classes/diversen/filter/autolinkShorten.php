<?php

namespace diversen\filter;

/**
 * file contains stacklink filter
 * @package filters
 */

/**
 * class contans stacklink filter
 * @package filters
 */

class autolinkShorten {
    
    /**
     * create auto links in a text
     * @param string $text to filter
     * @return string $text the filtered text
     */
    public static function filter($text) {
        $text = self::autoLink($text);
        return $text;
    }

    /**
     * autolink text
     * @param string $text to filter
     * @return string $text the filtered text
     */
    protected static function autoLink($text) {
        $pattern = '#\b(([\w-]+://?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#';
        return preg_replace_callback($pattern, array ('self', 'autoLinkCallback'), $text);
    }

    /**
     * following functions found on: http://stackoverflow.com/a/1971451
     * @param array $matches
     * @return string $text linkified url
     */
    protected static function autoLinkCallback($matches) {
        $max_url_length = 50;
        $max_depth_if_over_length = 2;
        $ellipsis = '&hellip;';

        $url_full = $matches[0];
        $url_short = '';

        if (strlen($url_full) > $max_url_length) {
            $parts = parse_url($url_full);
            $url_short = $parts['scheme'] . '://' . preg_replace('/^www\./', '', $parts['host']) . '/';

            $path_components = explode('/', trim($parts['path'], '/'));
            foreach ($path_components as $dir) {
                $url_string_components[] = $dir . '/';
            }

            if (!empty($parts['query'])) {
                $url_string_components[] = '?' . $parts['query'];
            }

            if (!empty($parts['fragment'])) {
                $url_string_components[] = '#' . $parts['fragment'];
            }

            for ($k = 0; $k < count($url_string_components); $k++) {
                $curr_component = $url_string_components[$k];
                if ($k >= $max_depth_if_over_length || strlen($url_short) + strlen($curr_component) > $max_url_length) {
                    if ($k == 0 && strlen($url_short) < $max_url_length) {
                        // Always show a portion of first directory
                        $url_short .= substr($curr_component, 0, $max_url_length - strlen($url_short));
                    }
                    $url_short .= $ellipsis;
                    break;
                }
                $url_short .= $curr_component;
            }
        } else {
            $url_short = $url_full;
        }

        return "<a target=\"_blank\" href=\"$url_full\">$url_short</a>";
    }
}
