<?php

/**
 * markdown filter. use Michelf markdown
 * @package    filters
 */

use \Michelf\Markdown;

/**
 * markdown filter.
 *
 * @package    filters
 */
class cosmarkdown {

    /**
     *
     * @param  string     string to markdown.
     * @return string
     */
    public static function filter($text){

        static $md = null;
        if (!$md){
            $md = new Markdown();
        }

        $md->no_entities = true;
        $md->no_markup = true;

        $text = $md->transform($text);
        //get_filtered_content($filter, $content);
	return $text; //$parser->transform($text);
    }
}

class filters_cosmarkdown extends cosmarkdown {}
