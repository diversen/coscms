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
class mdNormalizeLinks extends \Michelf\MarkdownExtra {

    public $attr = array();
    
    public $id_class_attr_catch_re = '\{((?:[ ]*[#.][-_:a-zA-Z0-9]+){1,})[ ]*\}';

    protected function doAnchors($text) {

        
        #
        # Turn Markdown link shortcuts into XHTML <a> tags.
        #
		if ($this->in_anchor)
            return $text;
        $this->in_anchor = true;

        #
        # First, handle reference-style links: [link text] [id]
        #
		$text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				(' . $this->nested_brackets_re . ')	# link text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]
			)
			}xs', array(&$this, '_doAnchors_reference_callback'), $text);

        #
        # Next, inline-style links: [link text](url "optional title")
        #
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  \[
				(' . $this->nested_brackets_re . ')	# link text = $2
			  \]
			  \(			# literal paren
				[ \n]*
				(?:
					<(.+?)>	# href = $3
				|
					(' . $this->nested_url_parenthesis_re . ')	# href = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# Title = $7
				  \6		# matching quote
				  [ \n]*	# ignore any spaces/tabs between closing quote and )
				)?			# title is optional
			  \)
			  (?:[ ]? ' . $this->id_class_attr_catch_re . ' )?	 # $8 = id/class attributes
			)
			}xs', array(&$this, '_doAnchors_inline_callback'), $text);

                
        #
        # Last, handle reference-style shortcuts: [link text]
        # These must come last in case you've also got [link text][1]
        # or [link text](/foo)
        #
		$text = preg_replace_callback('{
			(					# wrap whole match in $1
			  \[
				([^\[\]]+)		# link text = $2; can\'t contain [ or ]
			  \]
			)
			}xs', array(&$this, '_doAnchors_reference_callback'), $text);

        $this->in_anchor = false;
        return $text;
    }

    protected function _doAnchors_reference_callback($matches) {


        $whole_match = $matches[1];
        $link_text = $matches[2];
        $link_id = & $matches[3];


        if ($link_id == "") {
            # for shortcut links like [this][] or [this].
            $link_id = $link_text;
        }

        # lower-case and turn embedded newlines into spaces
        $link_id = strtolower($link_id);
        $link_id = preg_replace('{[ ]?\n}', ' ', $link_id);

        if (isset($this->urls[$link_id])) {
            $url = $this->urls[$link_id];
            $url = $this->encodeAttribute($url);



            $result = "<a rel=\"nofollow\" href=\"$url\"";
            if (isset($this->titles[$link_id])) {
                $title = $this->titles[$link_id];
                $title = $this->encodeAttribute($title);
                $result .= " title=\"$title\"";
            }

            $link_text = $this->runSpanGamut($link_text);
            $result .= ">$link_text</a>";
            $result = $this->hashPart($result);
        } else {
            $result = $whole_match;
        }
        return $result;
    }

    protected function _doAnchors_inline_callback($matches) {
        
        if (isset($matches[4])) {
            if (strstr($matches[4], "/content/article/view")) {
                //print_r($matches);
                $explode = explode("/", $matches[4]);
                if (is_numeric($explode[4])) {
                    $matches[4] = "#$explode[4]";
                    return "<a href=\"$matches[4]\">$matches[2]</a>";
                    // [http://link](title) // style
                    // return "[$matches[2]]($matches[4])";
                }
                
            }
        }
        
        //print_r($matches);
        return($matches[0]);
        //$exploded = explode("/", $matches[4]);
        //print_r($exploded); 
        
        $whole_match = $matches[1];
        $link_text = $this->runSpanGamut($matches[2]);
        $url = $matches[3] == '' ? $matches[4] : $matches[3];
        $title = & $matches[7];

        $url = $this->encodeAttribute($url);

        $result = "<a rel=\"nofollow\" href=\"$url\"";
        if (isset($title)) {
            $title = $this->encodeAttribute($title);
            $result .= " title=\"$title\"";
        }

        $link_text = $this->runSpanGamut($link_text);
        $result .= ">$link_text</a>";

        return $this->hashPart($result);
    }

    /**
     *
     * @param  string     string to markdown.
     * @return string
     */
    public static function filter($text) {

        static $md = null;
        if (!$md) {
            $md = new mdNormalizeLinks();
        }

        $md->no_entities = true;
        $md->no_markup = true;

        //$md->
        $text = $md->doAnchors($text);
        return $text;
    }

}

class filters_mdNormalizeLinks extends mdNormalizeLinks {
    
}
