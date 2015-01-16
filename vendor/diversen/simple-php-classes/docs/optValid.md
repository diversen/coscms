        <?php

        use diversen\cli\optValid;

        // parse commandline options with php 
        // command line options usaually start with - and --
        $str = "-s -S --chapters=7 -V geometry:margin=1in -V documentclass=memoir -V lang=danish";

        $allow = array (
            's' => null, 
            'S' => null, 
            'chapters' => null, 
            'V' => array ('sgeometry:margin', 'documentclass', 'lang'),
            );

        $o = new optValid();
        // split string into base args
        $ary = $o->split($str);

        // as array
        $ary = $o->getAry($ary);

        // sub options
        $ary = $o->setSubVal($ary);
        $ok = $o->isValid($ary, $allow);
        if(!$ok) {
            print_r($o->errors);
        }
        echo "OK";
        die;
        //print_r($ary); die;
