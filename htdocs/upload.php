<?php


$url = basename($_SERVER['SCRIPT_FILENAME']);
if (isset($_GET['progress_key'])) {
    if (!function_exists('apc_fetch')) {
        die('No such function');
    }
    $status = apc_fetch('upload_' . $_GET['progress_key']);
    if ($status['total'] == 0) {
        echo "0";
    } else {
        $total = $status['current'] / $status['total'] * 100;
        echo (int)$total;
    }
    die();
}

$_GET['up_id'] = htmlspecialchars($_GET['up_id']);

// Simple progress bar rewritten from:
// http://www.johnboy.com/php-upload-progress-bar/
// upload.php

?>
<!doctype html>
<head>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
    <link href="/templates/uikit/default/default.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/uikit/2.26.4/css/components/progress.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/uikit/2.26.4/js/uikit.min.js"></script>
    
    <script>

        $(document).ready(function () {
            setInterval(function () {
                // get request to the current URL (upload.php) 
                // which calls the code at the top of the page.  It checks the 
                // file's progress based on the file id "progress_key=" and 
                // returns the value with the function below:
                $.get("<?=$url?>?progress_key=<?=$_GET['up_id']?>&randval=" + Math.random(), function (data) {

                        var progress = parseInt(data);
                        console.log(progress);
                        $('.uk-progress-bar').attr('style', 'width: ' + data + '%');
                        $('.uk-progress-bar').html(data + '%');
                })
            }, 1000); 
        });


    </script>
</head>
<body style="margin:0px">
    <div class="uk-progress">
        <div class="uk-progress-bar"  style="width: 0%;">0%</div>
    </div>
</body>
