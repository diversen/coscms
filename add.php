<?php

if (!session::checkAccessControl('video_allow_edit')){
    return;
}

moduleloader::$referenceOptions = array ('edit_link' => 'true'); 
if (!moduleloader::includeRefrenceModule()){   
    moduleloader::$status['404'] = true;
    return;
}

$bytes = config::getModuleIni('video_max_size');
//echo transform_bytes($bytes);

// we now have a refrence module and a parent id wo work from.
$link = moduleloader::$referenceLink;

$headline = lang::translate('video_add_file') . MENU_SUB_SEPARATOR_SEC . $link;
html::headline($headline);

template::setTitle(lang::translate('video_add_file'));
$options = moduleloader::getReferenceInfo();

// set parent modules menu
layout::setMenuFromClassPath($options['reference']);

$video = new video($options);
$video->viewFileFormInsert();

$options['admin'] = true;
$rows = $video->getAllvideoInfo($options);
echo $video->displayAllVideo($rows, $options);
//print_r($rows);
