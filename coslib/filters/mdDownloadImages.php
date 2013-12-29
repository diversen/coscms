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
            $this->saveImage($url);
        }
    }

    protected function _doImages_inline_callback($matches) {
        $whole_match = $matches[1];
        $alt_text = $matches[2];
        $url = $matches[3] == '' ? $matches[4] : $matches[3];
        $title = & $matches[7];

        $alt_text = $this->encodeAttribute($alt_text);
        $url = $this->encodeAttribute($url);
        //echo $url;
        $this->saveImage($url);
    }

    protected function saveImage($url) {
        echo $url;
        
        $id = uri_direct::fragment(2, $url);
        $title = uri_direct::fragment(3, $url);

        echo $path = "/images/$id/$title";
        $save_path = config::getFullFilesPath($path);
        $image_url = config::getSchemeWithServerName() . $url;

        $file = file_get_contents($image_url);
        if ($file === false) {
            echo "Fejl";
        }

        // make dir 
        $dir = dirname($path);
        file::mkdir($dir);
        file_put_contents($save_path, $file);
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

        $text = $md->transform($text);
        return $text;
    }

}

class filters_mdDownloadImages extends mdDownloadImages {
    
}
