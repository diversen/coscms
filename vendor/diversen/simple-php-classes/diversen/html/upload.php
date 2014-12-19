<?php

namespace diversen\html;
//use diversen\template\assets;
/**
 * html upload using html5
 */

class upload {

    /**
     * html5 upload form. Standalone.
     * @param string $url
     * @return string $str html and script
     */
    public function fileHtml5($url) {
        template_assets::setJs('/js/jquery.html5_upload.js');
        $js = $this->getJs($url);
        template_assets::setStringJs($js);
        return $this->getHtml();
        
    }
    
    public function getHtml () {
        ob_start();
        ?>
        <input type="file" multiple="multiple" id="html5_upload_field" />
        <div id="progress_report">
            <div id="progress_report_name"></div>
            <div id="progress_report_status" style="font-style: italic;"></div>
            <div id="progress_report_bar_container" style="width: 90%; height: 5px;">
                <div id="progress_report_bar" style="background-color: blue; width: 0; height: 100%;"></div>
            </div>
            <div id="progress_report_final"></div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    public function getJs ($url) {

        ob_start();
        ?>
            $(function () {
                $("#html5_upload_field").html5_upload({
                    url: '<?= $url ?>',
                    fieldName: 'file',
                    sendBoundary: window.FormData || $.browser.mozilla,
                    onStart: function (event, total) {
                        return true;
                    },
                    onProgress: function (event, progress, name, number, total) {
                        console.log(progress, number);
                    },
                    setName: function (text) {
                        $("#progress_report_name").text(text);
                    },
                    setStatus: function (text) {
                        $("#progress_report_status").text(text);
                    },
                    setProgress: function (val) {
                        $("#progress_report_bar").css('width', Math.ceil(val * 100) + "%");
                    },
                    onFinishOne: function (event, response, name, number, total) {
                        $('#progress_report_final').text(response);
                    },
                    onError: function (event, name, error) {
                        alert('' + name);
                    }
                });
            });

        <?php
        return ob_get_clean();
    }
}
