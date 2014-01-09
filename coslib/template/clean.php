<?php

/**
 * file contains a clean template, which can be used
 * if we need to print a clean page
 * @package template
 */

/**
 * class contains a clean template, which can be used
 * if we need to print a clean page
 * @package template
 */
class template_clean {
    
    /**
     * echo the header
     */
    public static function header () { ?>
<!doctype html>
<html lang="<?=config::$vars['coscms_main']['lang']?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!--[if lt IE 9]>
	<script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
<![endif]-->
<title><?php echo template_assets::getTitle(); ?></title>

<?php

template_assets::setRelAsset('css', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.min.css');  
template_assets::setRelAsset('js', '//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js');  
template_assets::setRelAsset('js', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js');

echo template_assets::getRelAssets();
echo template_assets::getJsHead();
echo template_meta::getMeta();


//template_assets::setTemplateCss('zimpleza', null, 10);
template_assets::setJs('/js/jquery.ui.touch-punch.min.js');
template_assets::setCss('/templates/zimpleza/devices.css');
echo template_favicon::getFaviconHTML();
echo template_assets::getCompressedCss();
echo template_assets::getInlineCss();

?>

</head>
<body><?php
    }

    /**
     * eho the footer
     */
    public static function footer () {

echo template::getEndHTML();
echo template_assets::getCompressedJs();
echo template_assets::getInlineJs();

?>
</body>
</html><?php 
    }
}
