<?php

/**
 * File containing file backup functions for shell mode.
 *
 * commands can be used when using the command
 * <code>$ coscli.sh backup</code>
 * For options about the shell command, use
 * <code>$ coscli.sh backup -h</code>
 * For backup of database, you should use the db command.
 *
 * @package     shell
 */

/**
 * function for generaing tar archives
 *
 * All file settings are preserved. Archive will be placed in /backup/full dir.
 * You can specifiy an exact filename in options. If you don't use a filename
 * in the options array, the archive will be named after current timestamp, e.g.
 * backup/full/1264168904.tar
 *
 * @param   array   options to parser, e.g.
 *                  <code>array('File' => 'backup/full/latest.tar')</code> This will create
 *                  the backup file backup/full/latest.tar
 *                  Leave options empty if you want to use current timestamp for 
 *                  your achive.
 * @return  int     the executed commands shell status 0 on success. 
 */
function backup_backup($options){
    cos_needs_root();
    
    // default backup dir
    if (isset($options['File'])){
        // we use full path when specifing a file
        $backup_file = $options['File'];
    } else {
        $backup_file = "backup/full/" . time() . ".tar.gz";
    }
    $command = "tar -Pczf $backup_file --exclude=backup* -v . ";
    $ret = cos_exec($command);
}

/**
 * function for generaing tar archives
 *
 * All file settings are preserved. Archive will be placed in /backup/full dir.
 * You can specifiy an exact filename in options. If you don't use a filename
 * in the options array, the archive will be named after current timestamp, e.g.
 * backup/full/1264168904.tar
 *
 * @param   array   options to parser, e.g.
 *                  <code>array('File' => 'backup/full/latest.tar')</code> This will create
 *                  the backup file backup/full/latest.tar
 *                  Leave options empty if you want to use current timestamp for 
 *                  your achive.
 * @return  int     the executed commands shell status 0 on success. 
 */
function backup_files_backup($options){
    cos_needs_root();
    // default backup dir
    if (isset($options['File'])){
        // we use full path when specifing a file
        $backup_file = $options['File'];
    } else {
        $backup_file = "backup/files/" . time() . ".tar";
    }
    $command = "tar -Pcf $backup_file -v ./htdocs/files ";
    $ret = cos_exec($command);
}

/**
 * function for restoring tar archive
 *
 * All file settings are restored (if user is the owner of all files)
 *
 * @param   array   options to parser, e.g.
 *                  <code>array('File' => '/backup/full/latest.tar')</code> This will restore
 *                  the tar achive /backup/full/latest.tar
 *
 *                  Leave options empty if you
 *                  want to restore latest archive with highest timestamp, .e.g
 *                  backup/full/1264168904.tar
 * @return  int     the executed commands shell status 0 on success. 
 */
function backup_restore($options){
    
    cos_needs_root();
    if (!isset($options['File'])){
        $latest = backup_get_latest_backup();
        if ($latest == 0) die ("Yet no backups\n");
        $backup_file = $latest = "backup/full/" . $latest . ".tar.gz";
    } else {
        $backup_file = $options['File'];
    }

    $command = "tar -Pxzf $backup_file --exclude=backup* -v . ";
    $ret = cos_exec($command);
}

/**
 * function for restoring tar archive
 *
 * All file settings are restored (if user is the owner of all files)
 *
 * @param   array   options to parser, e.g.
 *                  <code>array('File' => '/backup/full/latest.tar')</code> This will restore
 *                  the tar achive /backup/full/latest.tar
 *
 *                  Leave options empty if you
 *                  want to restore latest archive with highest timestamp, .e.g
 *                  backup/full/1264168904.tar
 * @return  int     the executed commands shell status 0 on success. 
 */
function backup_files_restore($options){
    
    // in order to easily preserve ownership we use need to run as root
    cos_needs_root();
    if (!isset($options['File'])){
        $latest = backup_get_latest_backup('files');
        if ($latest == 0) die ("Yet no backups\n");
        $backup_file = $latest = "backup/files/" . $latest . ".tar";
    } else {
        $backup_file = $options['File'];
    }
    
    $command = "tar -Pxf $backup_file -v ./htdocs/files ";
    $ret = cos_exec($command);
}

/**
 * function for getting latest timestamp from /backup/full dir
 *
 * @return int   backup with most recent timestamp
 */
function backup_get_latest_backup($type = null){
    if ($type == 'files') {
        $dir = _COS_PATH . "/backup/files";
    } else {
        $dir = _COS_PATH . "/backup/full";
    }
    $list = file::getFileList($dir);
    $time_stamp = 0;
    foreach ($list as $key => $val){
        $file = explode('.', $val);
        if (is_numeric($file[0])){
            if ($file[0] > $time_stamp){
                $time_stamp = $file[0];
            }
        }
    }
    return $time_stamp;
}

// }}}

mainCli::setCommand('backup', array(
    'description' => "Command for making file based tar archive backups of system. ",
));

mainCli::setOption('backup_backup', array(
    'long_name'   => '--backup',
    'description' => "Will backup all files and preserve ownership, e.g. --backup test.tar. If you don't specify a file then the script will use current timestamp." ,
    'action'      => 'StoreTrue'
));

mainCli::setOption('backup_files_backup', array(
    'long_name'   => '--files-backup',
    'description' => "Will backup public html files in htdocs/files  and preserve ownership, e.g. --backup test.tar." ,
    'action'      => 'StoreTrue'
));

mainCli::setOption('backup_restore', array(
    'long_name'   => '--restore',
    'description' => 'Will restore all files from a backup and preserve ownership, e.g. --restore test.tar. If no file is given when restoring backup, then file with latest timestamp will be used',
    'action'      => 'StoreTrue'
));

mainCli::setOption('backup_files_restore', array(
    'long_name'   => '--files-restore',
    'description' => 'Will restore public htdocs/files from a backup and preserve ownership, e.g. --files-restore test.tar. I',
    'action'      => 'StoreTrue'
));

mainCli::setArgument('File',
    array('description'=> 'Specify a filename for the backup',
          'optional' => true));
 
