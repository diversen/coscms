<?php

class js_toc {
    
    /**
     * function to create a easy TOC for any module. 
     * @param array $options array ('exclude' => 'h1', 'content' => '#content_article'); 
     */
    public static function set ($options = array ()) {
        template::setJs('/js/js-toc/jquery.toc-1.1.4.js');
        //template::setCss('/js/js-toc/toc.css');
        if (!isset($options['exclude'])) {
            $options['exclude'] = '';
        }
        if (!isset($options['context'])) {
            $options['context'] = '#content';
        }
        $str = <<<EOF
    $(document).ready(function() {
        $('#toc').toc({exclude: '{$options['exclude']}' , context: '{$options['context']}', autoId: true, 'top': 'flaf'});
    });
EOF;

        
        template::setStringJs($str);
    }
}
