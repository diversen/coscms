<?php

/**
 * sanitize markdown headers according to a header. 
 * e.g. 1 (h1)
 * You will need to use mdSanitizeHeaders::filter($text, $level)
 */

class mdSanitizeHeaders {
    public $level = null;
    
    /**
     * sanitizes a markdowns headers. According to level
     * @param string $text markdown input.
     * @param int $level header level (1-6)
     * @return string $text markdown text
     */
    function filter ($text, $level) {
        $this->level = $level;
        $text = preg_replace('{\r\n?}', "\n", $text);
        return $this->doHeaders($text); 
    }
    
    /**
     * override do headers
     * @param string $text
     * @return string $text
     */
    protected function doHeaders($text) {
		# Setext-style headers:
		#	  Header 1
		#	  ========
		#  
		#	  Header 2
		#	  --------
		#
		$text = preg_replace_callback('{ ^(.+?)[ ]*\n(=+|-+)[ ]*\n+ }mx',
			array(&$this, '_doHeaders_callback_setext'), $text);

		# atx-style headers:
		#	# Header 1
		#	## Header 2
		#	## Header 2 with closing hashes ##
		#	...
		#	###### Header 6
		#
		$text = preg_replace_callback('{
				^(\#{1,6})	# $1 = string of #\'s
				[ ]*
				(.+?)		# $2 = Header text
				[ ]*
				\#*			# optional closing #\'s (not counted)
				\n+
			}xm',
			array(&$this, '_doHeaders_callback_atx'), $text);

		return $text;
	}
        
	protected function _doHeaders_callback_setext($matches) {
            $level = $this->level;
            $header = str_repeat('#', $level) . " ";
            return $header . $matches[1] . "\n\n";

	}
        
	protected function _doHeaders_callback_atx($matches) {
            $level = $this->level;
            $header = str_repeat('#', $level) . " ";
            return $header . $matches[2] . "\n\n";
	}
}

class filters_mdSanitizeHeaders extends mdSanitizeHeaders {}
