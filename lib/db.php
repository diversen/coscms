<?php

/**
 * File contains contains class for connecting to a mysql database
 * with PDO and doing basic crud operations and simple search operations. 
 *
 * @package    coslib
 */

/**
 * Class contains contains methods for connecting to a mysql database
 * with PDO and doing basic crud operations and simple search operations. 
 * Almost any build in modules extends this class. 
 * 
 * @package    coslib
 */
class db {
    /**
     * Database handle for the database connection. 
     *
     * static $dbh that holds the connection to the database
     * @var false|object
     */
    static $dbh = false;

    /**
     *
     * @var  array  holds all sqlstatements when in debug mode.
     */
    static $debug = array();


    /**
     * constructor will try to call method connect
     */
    public function __construct(){

    }

    /**
     *
     * @return array    all sql statements as an array
     */
    static function getDebug() {
        return self::$debug;
    }

    /**
     * Method for connecting a mysql database
     * if a connection is open we use that connection
     * connection string is read from config/config.ini
     *
     * @global <array> $_COS_MAIN is holding the info about the database
     */
    public function connect(){
        //global $_COS_MAIN;
        self::$debug[] = "Trying to connect with " . register::$vars['coscms_main']['url'];
        try {
            self::$dbh = new PDO(
                //$_COS_MAIN['url'],
                //$_COS_MAIN['username'],
                //$_COS_MAIN['password']
                register::$vars['coscms_main']['url'],
                register::$vars['coscms_main']['username'],
                register::$vars['coscms_main']['password']

            );
            //PDO::MYSQL_ATTR_USE_BUFFERED_QUERY = true;
            self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if(!self::$dbh) $this->connect();
        } catch (PDOException $e) {
            $this->fatalError ('Connection failed: ' . $e->getMessage());
        }
        self::$debug[]  = 'Connected!';
    }
    
    /**
     * Method for selecting one row for a table. 
     *
     * @param string the tablename to select from (e.g. auth)
     * @param string fieldname the field to search from (e.g username)
     * @param string simple search conditions for the fieldname (e.g. admin) 
     *        'select * from auth where username = admin'
     * @param array fields the fields to select else * 
     * @return array|0 the fetched row or 0 if no rows matched the search
     */
    public function selectOne($table, $fieldname=null, $search=null, $fields=null){
        $rows = $this->select($table, $fieldname, $search, $fields);
        foreach ($rows as $row){
            if (!empty($row)){
                return $row;
            } else {
                return 0;
            }
        }
    }

    /**
     * Method for easy selecting from one table
     *
     * @param   string          table the tablename (auth)
     * @param   string          fieldname the field to search from (username)
     * @param   string|array    search simple search conditions for the fieldname (admin)
     *                          'select * from auth where username = admin' or
     * @param   array           the fields to select else * 'select id, title ... '
     * @return  array $rows fetched
     */
    public function select($table, $fieldname=null, $search=null, $fields=null){
            if ($fields){
                $fields = implode(' ,', $fields);
                $sql = "SELECT " . $fields . " FROM ";
            } else {
                $sql = "SELECT * FROM ";
            }
            
            $sql .= "`$table` WHERE ";
            if (is_array($search)){
                foreach ($search as $key => $val){
                //array('username' => 1, $key => '2343');
                    $params[] ="`$key`=:$key";
                }
                $params = implode(' AND ', $params);
                $sql .= $params;
            } else {
                //
                $sql .= "`$fieldname`=:search";
            }
            self::$debug[]  = "Trying to prepare select sql: $sql";
            $stmt = self::$dbh->prepare($sql);

            if (is_array($search)){
                foreach ($search as $key => $val){
                    $stmt->bindValue (":$key", $val);
                }
            } else {
                $stmt->bindParam(':search', $search);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rows;
    }
    
    /**
     * Method for deleting from a database table
     *
     * @param   string  table the database table to delete from .e.g. auth
     * @param   string  fieldname the where clause e.g. id
     * @param   string  search which rows should be deleted (3) e.g.
     *                  delete from 'auth' Where id = 3
     * @return  boolean true on succes or false on failure
     */
    public function delete($table, $fieldname, $search){

            $sql = "DELETE FROM `$table` WHERE `$fieldname`=:search";
            self::$debug[]  = "Trying to prepare delete sql: $sql";
            $stmt = self::$dbh->prepare($sql);
            $stmt->bindParam(':search', $search);
            $ret = $stmt->execute();
            return $ret;
    }

    /**
     * Method for seleting all with the options for adding a limit and a order
     *
     * @param   string      table the table were we want to select all from
     * @param   array       fields to select
     * @param   array       an array with search options e.g. 
     *                      <code>array ('username' => 'admin', 'email' => 'dennis@coscms.org');</code>
     * @param   int         from where from do we select
     * @param   int         limit set per select
     * @param   string      order_by field to order by
     * @param   asc         boolean (1 or 0)
     * @return  array|false rows ASSOC array containing the selected row or false
     */
    public function selectAll($table, $fields = null, $search = null, $from = null,
                              $limit = null, $order_by = null, $asc = null){
            if ($fields){
                $fields = implode(' ,', $fields);
                $sql = "SELECT " . $fields . " FROM `$table`";
            } else {
                $sql = "SELECT * FROM `$table` ";
            }

            $sql .= " WHERE ";
            if (is_array($search)){
                foreach ($search as $key => $val){
                    $params[] ="`$key`=:$key";
                }
                $params = implode(' AND ', $params);
                $sql .= $params;
            } else if (is_string($search)){
                $sql.= ' ' . $search . ' ';
            } else {
                $sql.= " 1=1 ";
            }

            if ($order_by){
                self::$dbh->quote($order_by);
                $sql.= " ORDER BY `$order_by` ";
                if ($asc == 1){
                    $sql.= "ASC ";
                } else {
                    $sql.= "DESC ";
                }

            }

            if (isset($from)){
                $sql.= "LIMIT $from, $limit";
            }
            self::$debug[]  = "Trying to prepare selectAll sql: $sql";
            try {     
                $stmt = self::$dbh->prepare($sql);
                if (is_array($search)){
                    foreach ($search as $key => $val){
                        $stmt->bindValue (":$key", $val);
                    }
                }
                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $this->fatalError ($e->getMessage());
            }
        return $rows;
    }

    /**
     * Method for inserting values into a table
     *
     * @param   string  table the table to insert into
     * @param   array   values to insert, .e.g <code>array ('username' => 'test', 'password' => md5('test'))</code>
     * @return  boolean true or false
     */
    public function insert($table, $values, $bind = null){
        $fieldnames = array_keys($values);
        $sql = "INSERT INTO $table";
        $fields = '( ' . implode(' ,', $fieldnames) . ' )';
        $bound = '(:' . implode(', :', $fieldnames) . ' )';
        $sql .= $fields.' VALUES '.$bound;
        self::$debug[]  = "Trying to prepare insert sql: $sql";
        $stmt = self::$dbh->prepare($sql);
        // bind speciel params
        if (isset($bind) && is_array($bind)){
            foreach ($values as $key => $val){
                if (isset($bind[$key])){
                    $stmt->bindParam(":".$key, $values[$key], $bind[$key]);
                } else {
                    $stmt->bindParam(":".$key, $values[$key]);
                }
            }
            $ret = $stmt->execute();
        } else {
            $ret = $stmt->execute($values);
        }
        return $ret;
    }
    /**
     * Method for doing a simple full-text mysql search in a database table
     *
     * @param   string  table the table to search e.g. 'article'
     * @param   string  match what to match, e.g 'title, content'
     * @param   string  select what to select e.g. '*'
     * @param   string  search what to search for e.g 'some search words'
     * @param   int     from where to start getting the results
     * @param   int     limit how many results to fetch e.g. 20
     * @return  array   rows FETCH_ASSOC array of rows
     */
    public function simpleSearch($table, $match, $search, $select, $from, $limit ){
        $query = "SELECT ";
        if (empty($select)){
            $select = '*';
        }
        $query.= "$select, ";
        $query.= "MATCH ($match) ";
        $query.= "AGAINST (:search) AS score ";
        $query.= "FROM $table ";
        $query.= "WHERE MATCH ($match) AGAINST (:search) ";
        $query.= "ORDER BY score DESC ";
        $query.= "LIMIT $from, $limit";
        self::$debug[]  = "Trying to prepare simpleSearch sql: $query";
        try {
            $stmt = self::$dbh->prepare($query);
            $stmt->bindParam(':search', $search);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            $this->fatalError ($e->getMessage());
        }
        return $rows;
    }

    /**
     * Method for counting rows when searching a mysql table with full-text
     *
     * @param   string  table the table to search e.g. 'article'
     * @param   string  match what to match, e.g 'title, content'
     * @param   string  search what to search for e.g 'some search words'
     * @return  int     num rows of search results from used full-text search
     */
    public function simpleSearchCount($table, $match, $search ){
        $query = "SELECT COUNT(*) AS num_rows ";
        $query.= "FROM $table ";
        $query.= "WHERE MATCH ($match) AGAINST (:search) ";
        self::$debug[] = "Trying to prepare simpleSearchCount sql: $query in ";
        $stmt = self::$dbh->prepare($query);
        $stmt->bindParam(':search', $search);
        $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($row as $key => $val){
            return $val['num_rows'];
        }
    }

    /**
     * Function for updating a row in a table
     * @todo    bug when using arrays as search.
     * 
     * @param   string  table the table to insert into
     * @param   array   values to update
     * @param   int     primary id of row to be updated e.g array ('username' => 'test', 'password' => md5('test'))
     * @return  int     value of last updated row
     */
    public function update($table, $values, $search, $bind = null){
        $fieldnames = array_keys($values);
        $sql = "Update `$table` SET ";

        foreach ($values as $field => $value ){
            $ary[] = " `$field`=" . ":$field ";
        }
        
        $sql .= implode (',', $ary);
        $sql .= " WHERE ";

        if (is_array($search)){
            foreach ($search as $key => $val){
                //array('username' => 1, $key => '2343');
                $params[] ="`$key`=:$key";
            }
            $params = implode(' AND ', $params);
            $sql .= $params;
        } else {
            $search = self::$dbh->quote($search);
            $sql .= " `id` = $search";
        }
        self::$debug[]  = "Trying to prepare update sql: $sql";
        $stmt = self::$dbh->prepare($sql);

        // bind speciel params if set
        if (isset($bind) && is_array($bind)){
            foreach ($values as $key => $val){
                if (isset($bind[$key])){
                    $stmt->bindParam(":".$key, $values[$key], $bind[$key]);
                } else {
                    $stmt->bindParam(":".$key, $values[$key]);
                }
            }
            $ret = $stmt->execute();
        } else {
            $ret = $stmt->execute($values);
        }
        return $ret;
    }

    /**
     * Method for counting rows in a table
     *
     * @param   string  table to count number of rows in
     * @return  int     num_rows number of rows
     */
    public function getNumRows($table, $where = null){
        if (!isset($where)) $where = array();
        $sql = "SELECT count(*) as num_rows FROM `$table`";
        if (!empty($where) && is_array($where)){
            $sql.= "WHERE ";
            foreach ($where as $key => $val){
                $params[] ="`$key`=:$key";
            }
            $params = implode(' AND ', $params);
            $sql .= $params;
        }

        self::$debug[]  = "Trying to prepare getNumRows sql: $sql";
        $stmt = self::$dbh->prepare($sql);
        foreach ($where as $key => $val){
            $stmt->bindValue (":$key", $val);
        }
        $ret = $stmt->execute();
        $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $row[0]['num_rows'];
    }


    /**
     * Method for performing a direct selectQuery, e.g. if we are joinging rows
     *
     * @param   string  The query to execute
     * @return  array   the rows found
     *
     */
    public function selectQuery($sql){
        self::$debug[]  = "Trying to prepare selectQuery sql: $sql";
        $stmt = self::$dbh->query($sql);
        $ret = $stmt->execute();
        //return $ret;
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    /**
     * Method for doing a raw query. Anything will go
     *
     * @param   string  the query to execute
     * @return  object  the stmt object returned from the query
     */
    public function rawQuery($sql){
        self::$debug[]  = "Trying to prepare rawQuery sql: $sql";
        $stmt = self::$dbh->query($sql);
        return $stmt;
    }

    /**
     * Method for preparing raw <code>$_POST vars</code> for execution 
     *
     * @return  array   values to use in update and insert sql commands.
     *                  we do not use 'submit' button and 'captcha'.
     */
    static public function prepareToPost(){
        self::$debug[] = "Trying to prepareToPost";
        $ary = array();
        foreach ($_POST as $key => $value){
            // continue if field value is 'submit' or 'captcha'
            if ($key == 'submit') continue;
            if ($key == 'captcha') continue;
            $ary[$key] = $value;
        }
        return $ary;
    }

    /**
     * Method for preventing cloning of the db instance
     */
    private function __clone(){
        $this->fatalError('Clone is not allowed.', E_USER_ERROR);
    }

    /**
     * Method for showing errors
     *
     * @todo    add     option for production and development
     * @param   string  msg the message to show with the backtrace
     */
    protected function fatalError($msg) {
        self::$debug[] = "Fatal error encountered";
        echo "<pre>Error!: $msg\n";
        $bt = debug_backtrace();
        foreach($bt as $line) {
            $args = var_export($line['args'], true);
            echo "{$line['function']}($args) at {$line['file']}:{$line['line']}\n";
        }
        echo "</pre>";
        die();
    }
}
