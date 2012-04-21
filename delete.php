<?php

if (!session::checkAccessControl('video_allow_edit')){
    return;
}

moduleLoader::$referenceOptions = array ('edit_link' => 'true');
if (!moduleLoader::includeRefrenceModule()){   
    moduleLoader::$status['404'] = true;
    return;
}

$link = moduleLoader::$referenceLink;
$headline = lang::translate('video_delete_file') . MENU_SUB_SEPARATOR_SEC . $link;
headline_message($headline);

template::setTitle(lang::translate('video_add_file'));

$options = moduleLoader::getReferenceInfo();
video::setFileId($frag = 3);
$video = new video($options);
$video->viewFileFormDelete();
