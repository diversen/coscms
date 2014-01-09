<?php

/**
 * work in progress
 * @ignore
 * @package parallel
 */

/**
 * work in progress
 * @ignore
 * @package parallel
 */
class parallel {
    
    
    public function generateTopDir () {
        $top_dir = 'tmp/pid';
        if (!file_exists($top_dir)) {
            mkdir($top_dir);
        }
    }
    
    public function generatePidDir () {
        $piddir = 'tmp/pid/' . uniqid() . '/';
        mkdir($piddir);
        return $piddir;
    }
    /**
     * command being called from CLI
     * sends  'default' (and in 'multiple processes') mails
     */
    public function run ($params) {

        if (empty($params)) {
            exit(0);
        }

        // generate a process dir
        $this->generateTopDir();
        $piddir = $this->generatePidDir();


        $proc_max = 20;
        $i = 0;
        while(true) {  

            $num = $this->numProcesses($piddir);
            if ($num > $proc_max) {
                sleep(1);
                continue;
            }  

            if ($i >= $num_domains) {
                break;
            }

            $domain = $rows[$i]['domain'];
            mailer_start_job($domain, $piddir, $date, $hour);
            $i++;

            log::error("Doing domain number $i = $domain");
        }

        // finsihed - deleted pid dir. 
        // file::rrmdir($piddir);
        exit(0);

    }
    
    /**
     * returns number of processes running
     * @return int $num porcesses
     */    
    public function numProcesses ($piddir) {
        $files = file::getFileList($piddir);
        return count($files);

    }
}
