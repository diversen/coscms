<?php

if (!session::checkAccessControl('video_allow_edit')){
    return;
}

if (!moduleLoader::includeRefrenceModule()){   
    moduleLoader::$status['404'] = true;
    return;
}

moduleLoader::$referenceOptions = array ('edit_link' => 'true');

$link = moduleLoader::$referenceLink;
$headline = lang::translate('video_edit_file') . MENU_SUB_SEPARATOR_SEC . $link;
headline_message($headline);

template::setTitle(lang::translate('video_edit_file'));

$options = moduleLoader::getReferenceInfo();

video::setFileId($frag = 3);
$video = new video($options);
$video->viewFileFormUpdate();
