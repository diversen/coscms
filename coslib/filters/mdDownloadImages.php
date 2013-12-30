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
class mdDownloadImages extends \Michelf\Markdown {

    /**
     * if set images will be downloaded to file system
     * @var $download 
     */
    public static $download = null;
    
    /**
     * 
     * if set we return raw markdown
     * @var type 
     */
    public static $getRaw = null;

    protected function _doImages_reference_callback($matches) {
        $whole_match = $matches[1];
        $alt_text = $matches[2];
        $link_id = strtolower($matches[3]);

        if ($link_id == "") {
            $link_id = strtolower($alt_text); # for shortcut links like ![this][].
        }

        $alt_text = $this->encodeAttribute($alt_text);
        if (isset($this->urls[$link_id])) {
            $url = $this->encodeAttribute($this->urls[$link_id]);
            if (self::$download) {
                $url = $this->saveImage($url);
                if (self::$getRaw) {
                    $url = _COS_HTDOCS . "$url";
                    return "![$alt_text]($url)";
                }
            }
            $result = "<img class=\"media_image\" src=\"$url\" alt=\"$alt_text\"";
            if (isset($this->titles[$link_id])) {
                $title = $this->titles[$link_id];
                $title = $this->encodeAttribute($title);
                $result .= " title=\"$title\"";
            }
            $result .= $this->empty_element_suffix;
            $result = $this->hashPart($result);
        } else {
            # If there's no such link ID, leave intact:
            $result = $whole_match;
        }

        return $result;
    }

    protected function _doImages_inline_callback($matches) {
        $whole_match = $matches[1];
        $alt_text = $matches[2];
        $url = $matches[3] == '' ? $matches[4] : $matches[3];
        $title = & $matches[7];

        $alt_text = $this->encodeAttribute($alt_text);
        $url = $this->encodeAttribute($url);
        if (self::$download) {

            $url = $this->saveImage($url);
            
            if (self::$getRaw) {
                
                $url = _COS_HTDOCS . "$url";
                
                return "![$alt_text]($url)";
            }
        }
        $result = "<img class=\"media_image\" src=\"$url\" alt=\"$alt_text\"";
        if (isset($title)) {
            $title = $this->encodeAttribute($title);
            $result .= " title=\"$title\""; # $title already quoted
        }
        $result .= $this->empty_element_suffix;

        return $this->hashPart($result);
    }

    protected function doImages($text) {
        #
        # Turn Markdown image shortcuts into <img> tags.
        #
		#
		# First, handle reference-style labeled images: ![alt text][id]
        #
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				(' . $this->nested_brackets_re . ')		# alt text = $2
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)		# id = $3
			  \]

			)
			}xs', array(&$this, '_doImages_reference_callback'), $text);

        #
        # Next, handle inline images:  ![alt text](url "optional title")
        # Don't forget: encode * and _
        #
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !\[
				(' . $this->nested_brackets_re . ')		# alt text = $2
			  \]
			  \s?			# One optional whitespace character
			  \(			# literal paren
				[ \n]*
				(?:
					<(\S*)>	# src url = $3
				|
					(' . $this->nested_url_parenthesis_re . ')	# src url = $4
				)
				[ \n]*
				(			# $5
				  ([\'"])	# quote char = $6
				  (.*?)		# title = $7
				  \6		# matching quote
				  [ \n]*
				)?			# title is optional
			  \)
			)
			}xs', array(&$this, '_doImages_inline_callback'), $text);

        return $text;
    }

    protected function saveImage($url) {

        $id = uri_direct::fragment(2, $url);
        $title = uri_direct::fragment(3, $url);

        $path = "/images/$id/$title";
        $save_path = config::getFullFilesPath($path);
        $web_path = config::getWebFilesPath($path);
        $image_url = config::getSchemeWithServerName() . $url;

        $file = file_get_contents($image_url);
        if ($file === false) {
            log::error('Could not get file content ' . $file);
            return '';
        }

        // make dir 
        $dir = dirname($path);
        file::mkdir($dir);
        file_put_contents($save_path, $file);
        return $web_path;
    }

    /**
     *
     * @param  string     string to markdown.
     * @return string
     */
    public static function filter($text) {

        static $md = null;
        if (!$md) {
            $md = new mdDownloadImages();
        }

        $md->no_entities = true;
        $md->no_markup = true;
        
        if (isset(self::$getRaw)) {
            return $md->doImages($text); 
        }

        $text = $md->transform($text);
        return $text;
    }

}

class filters_mdDownloadImages extends mdDownloadImages {
    
}
