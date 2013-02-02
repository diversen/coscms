<?php


use \Michelf\Markdown;

/**
 * markdown filter.
 *
 * @package    filter_markdown
 */
class cosmarkdown {

    /**
     *
     * @param  string     string to markdown.
     * @return string
     */
    public function filter($text){

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
