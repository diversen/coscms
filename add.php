<?php

if (!session::checkAccessControl('video_allow_edit')){
    return;
}

moduleLoader::$referenceOptions = array ('edit_link' => 'true'); 
if (!moduleLoader::includeRefrenceModule()){   
    moduleLoader::$status['404'] = true;
    return;
}

$bytes = config::getModuleIni('video_max_size');
//echo transform_bytes($bytes);

// we now have a refrence module and a parent id wo work from.
$link = moduleLoader::$referenceLink;

$headline = lang::translate('video_add_file') . MENU_SUB_SEPARATOR_SEC . $link;
headline_message($headline);

template::setTitle(lang::translate('video_add_file'));
$options = moduleLoader::getReferenceInfo();

$video = new video($options);
$video->viewFileFormInsert();

$options['admin'] = true;
$rows = $video->getAllvideoInfo($options);
echo $video->displayvideo($rows, $options);
//print_r($rows);
