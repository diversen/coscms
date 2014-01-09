<?php

/**
 * file contains class for adding js emoticons
 * to a document
 * @package js
 */

/**
 * You will need this: 
 * cd htdocs/templates
 * git clone git://github.com/JangoSteve/jQuery-CSSEmoticons.git emoticons
 * file contains class for adding js emoticons to a document
 * @package js
 */

class js_emoticons {
    public static function setAssets($select = '#content'){
            
          template_assets::setCss("/templates/emoticons/stylesheets/jquery.cssemoticons.css");
          template_assets::setJsHead("/templates/emoticons/javascripts/jquery.cssemoticons.js");
          $js = <<<EOF
$(document).ready(function(){
      $('$select').emoticonize({
	//delay: 800,
        animate: false
        //exclude: 'pre, code, .no-emoticons'
      });
    })
EOF;
          
          template_assets::setStringJs($js);
    }
}