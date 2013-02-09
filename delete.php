<?php

if (!session::checkAccessControl('video_allow_edit')){
    return;
}

moduleloader::$referenceOptions = array ('edit_link' => 'true');
if (!moduleloader::includeRefrenceModule()){   
    moduleloader::$status['404'] = true;
    return;
}

$link = moduleloader::$referenceLink;
$headline = lang::translate('video_delete_file') . MENU_SUB_SEPARATOR_SEC . $link;
html::headline($headline);

template::setTitle(lang::translate('video_add_file'));

$options = moduleloader::getReferenceInfo();
video::setFileId($frag = 3);
$video = new video($options);
$video->viewFileFormDelete();
