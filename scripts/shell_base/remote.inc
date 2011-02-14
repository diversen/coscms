<?php

/**
 * File containing remote functions for shell mode
 * (for moving file with ssh to server)
 *
 * @package     shell
 */

// {{{ move_to_remote()
/**
 * function for moving local files to remote via ssh server
 *
 * @return int  value from exec command
 */
function move_to_remote(){

    if (empty(register::$vars['coscms_main']['ssh_user'])){
        register::$vars['coscms_main']['ssh_user'] = getenv('USER');
    }

    $command = "rsync -e 'ssh -p " . register::$vars['coscms_main']['ssh_port'] . "' -avl ";
    $command.= "./* "; // move all except hidden files
    $command.= 
        register::$vars['coscms_main']['ssh_user'] . "@" .
        register::$vars['coscms_main']['ssh_host'] . ":" .
        register::$vars['coscms_main']['prod_path'];
    passthru($command, $ret);
}
// }}}
// {{{ move_to_remote()
/**
 * function for moving local files to remote via ssh server
 *
 * @return int  value from exec command
 */
function move_to_remote_latest_dump(){

    if (empty(register::$vars['coscms_main']['ssh_user'])){
        register::$vars['coscms_main']['ssh_user'] = getenv('USER');
    }

    $command = "rsync -e 'ssh -p " . register::$vars['coscms_main']['ssh_port'] . "' -avl ";
    $command.= "./backup/sql/* "; // move all except hidden files
    $command.=
        register::$vars['coscms_main']['ssh_user'] . "@" .
        register::$vars['coscms_main']['ssh_host'] . ":" .
        register::$vars['coscms_main']['prod_path'];
    passthru($command, $ret);
}
// }}}
// {{{ load_remote()
/**
 * function for creating latest dump,
 *          moving local files to remote via ssh server
 *          loading latest dump on production server. 
 *
 * @return int  value from exec command
 */
function load_remote(){
    // remove remote files
    //rm_files_remote();
    // create current dump
    dump_db_file();
    // move backup/sql/*
    move_to_remote_latest_dump();
    // create ssh command for loading latest db
    $command = 'ssh -p' . register::$vars['coscms_main']['ssh_port'] . ' ';
    $command.= getenv('USER') . '@' . register::$vars['coscms_main']['ssh_host'] . ' ';
    $command.= '"';
    $command.= "cd ". register::$vars['coscms_main']['prod_path'] . "; ./coscli.sh db --load-db-file;";
    $command.= "./coscli.sh file --chmod-files";
    $command.= '"' . "\n";
    $ret = cos_system($command);
    return $ret;
}

// }}}
// {{{ rm_files_remote()
/**
 * function for removing files on remote server
 * practical before using rsync for moving files.
 *
 * @return int  value from exec command
 */
function rm_files_remote(){
    $command = 'ssh -p' . register::$vars['coscms_main']['ssh_port'] . ' ';
    $command.= getenv('USER') . '@' . register::$vars['coscms_main']['ssh_host'] . ' ';
    $command.= '"';
    $command.= "cd " . register::$vars['coscms_main']['prod_path'] . ";";
    $command.= "sudo ./coscli.sh file --rm";
    $command.= '"' ."\n";
    $ret = cos_system($command);
    return $ret;
}

// }}}
// {{{ load_locale()
/**
 * function for creating latest dump,
 *          moving remote dump from remote to locale via ssh server.
 *
 * @return int  value from exec command
 */
function load_locale(){

    if (empty(register::$vars['coscms_main']['ssh_user'])){
        register::$vars['coscms_main']['ssh_user'] = getenv('USER');
    }

    // create remote latest.sql
    if (!confirm_readline('Locale database will be destroyed ')){
        exit(0);
    }

    $command = 'ssh -p' . register::$vars['coscms_main']['ssh_port'] . ' ';
    $command.= register::$vars['coscms_main']['ssh_user'] . '@' . register::$vars['coscms_main']['ssh_host'] . ' ';
    $command.= '"';
    $command.= "cd " . register::$vars['coscms_main']['prod_path'] . ";";
    $command.= "./coscli.sh db --dump-db-file ./backup/sql/latest.sql;";
    $command.= '"' ."\n";
    $ret = cos_system($command);

    // set locale public files to be owned by user
    $command = "";
    $command = "rsync -e 'ssh -p " . register::$vars['coscms_main']['ssh_port'] . "' -avl ";
    $command.= 
        register::$vars['coscms_main']['ssh_user'] . "@" .
        register::$vars['coscms_main']['ssh_host'] . ":" .
        register::$vars['coscms_main']['prod_path'];
    $command.= "/backup/sql/latest.sql ./backup/sql/latest.sql";
    $ret = cos_system($command);

    // reload locale db
    load_db_file(array('File' => './backup/sql/latest.sql'));

    //change files back to secure settings
}
// }}}
/**
 * function for creating latest dump,
 *          moving remote files to locale via ssh server
 *          loading latest sql dump on production server.
 *
 * @return int  value from exec command
 */
function move_to_locale(){
    if (empty(register::$vars['coscms_main']['ssh_user'])){
        register::$vars['coscms_main']['ssh_user'] = getenv('USER');
    }

    // create remote latest.sql
    if (!confirm_readline('Locale database will be destroyed ')){
        exit(0);
    }

    $command = 'ssh -p' . register::$vars['coscms_main']['ssh_port'] . ' ';
    $command.= 
        register::$vars['coscms_main']['ssh_user'] . '@' .
        register::$vars['coscms_main']['ssh_host'] . ' ';
    $command.= '"';
    $command.= "cd ".  register::$vars['coscms_main']['prod_path'] . ";";
    $command.= "./coscli.sh db --dump-db-file ./backup/sql/latest.sql;";
    $command.= "./coscli.sh file --chmod-files ";
    $command.= '"' ."\n";
    $ret = cos_system($command);

    // set locale public files to be owned by user
    chmod_files_to_owner();
    $command = "";
    $command = "rsync -e 'ssh -p " . register::$vars['coscms_main'][ssh_port] . "' -avl ";
    $command.= 
        register::$vars['coscms_main']['ssh_user'] . "@" .
        register::$vars['coscms_main']['ssh_host'] . ":" .
        register::$vars['coscms_main']['prod_path'];
    $command.= "/* .";
    $ret = cos_system($command);

    // reload locale db
    load_db_file(array('File' => './backup/sql/latest.sql'));

    //change files back to secure settings
    chmod_files();

}
// }}}

mainCli::setCommand('remote', array(
    'description' => "Commands for moving files between servers. ",
));

mainCli::setOption('move_to_remote', array(
    'long_name'   => '--move-to-remote',
    'description' => 'Will move complete source tree to remote ssh server',
    'action'      => 'StoreTrue'
));

mainCli::setOption('load_remote', array(
    'long_name'   => '--load-remote',
    'description' => 'Will load latest dump onto production server',
    'action'      => 'StoreTrue'
));

mainCli::setOption('move_to_locale', array(
    'long_name'   => '--move-to-locale',
    'description' => 'Will get remote file and latest sql from production to locale server',
    'action'      => 'StoreTrue'
));

mainCli::setOption('load_locale', array(
    'long_name'   => '--load-locale',
    'description' => 'Will get and load latest dump from production to locale server',
    'action'      => 'StoreTrue'
));