<?php
if (!empty($_FILES)) {

    $finfo = new finfo(FILEINFO_MIME);
    echo $mime = $finfo->file($_FILES['uploaded_files']['tmp_name']);  
    print_r($_FILES);
}
?>