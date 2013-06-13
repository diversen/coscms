<?php


/* 
 * cd htdocs/templates
 * git clone git://github.com/JangoSteve/jQuery-CSSEmoticons.git emoticons
 * 
 * if you build a profile then the repo will be enabled, when rebuilding
 * your profile.
 */

class coslib_emoticons {
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