<?php


/**
 * File contains contains class for connecting to a mysql database
 * with PDO and doing basic crud operations and simple search operations. 
 *
 * @package    db
 */

/**
 * Class contains contains methods for connecting to a mysql database
 * with PDO and doing basic crud operations and simple search operations. 
 * Almost any build in modules extends this class. 
 * 
 * @package    db
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
     * holds all sqlstatements
     * @var  array  $debug
     */
    static $debug = array();

    /**
     * gets a db object. Mostly so that we can use the db class in the static 
     * short hand way: db::init()->selectAll(self::$dbTable) 
     * @param array $options options to give constructor
     * @return object $db
     */
    static public function init($options = array ()){
        static $db = null;
        if (!$db) {
            $db = new db($options);
        } 
        return $db;
    }

    
    /**
     * constructor will try to call method connect
     * @param array $options
     */
    public function __construct($options = null){

    }
    
    /**
     * begin transaction
     * @return boolean $res
     */
    public static function begin () {
        return db::$dbh->beginTransaction();
    }
    
    /**
     * commit transaction
     * @return boolean $res 
     */
    public static function commit () {
        return db::$dbh->commit();
    }

    /**
     * roolback transaction
     * @returres boolean $res 
     */
    public static function rollback () {
        return db::$dbh->rollBack();
    }
    
    /**
     * return last insert id. 
     * @return int $lastinsertid last insert id
     */
    public static function lastInsertId () {
        return db::$dbh->lastInsertId();
    }

    /**
     * return all sql statements as an array
     * @return array $debug
     */
    static function getDebug() {
        return self::$debug;
    }

    /**
     * Method for connecting a mysql database
     * if a connection is open we use that connection
     * connection string is read from config/config.ini
     * 
     * @param array $options You can prevent the connection from halting on 
     *              error by setting $options['dont_die'] = true. If there
     *              is no connection NO_DB_CONN will be returned
     *              
     *              if you set $options[url] the class will try to connect to
     *              database with connection string given, e.g. 
     *              
     *              $options = array ('url' => 'conn_url',
     *                                'username => 'username',
     *                                'password' => 'password);
     * 
     * 
     * 
     *
     */
    public static function connect($options = null){
        self::$debug[] = "Trying to connect with " . config::$vars['coscms_main']['url'];
        if (isset($options['url'])) {
            $url = $options['url'];
            $username = $options['username'];
            $password = $options['password'];
        } else {
            $url = config::getMainIni('url');
            $username = config::getMainIni('username');
            $password = config::getMainIni('password');
            
        }

        try {
            self::$dbh = new \PDO(
                $url,
                $username,
                $password
            );
            self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if (isset(config::$vars['coscms_main']['db_init'])) {
                self::$dbh->exec(config::$vars['coscms_main']['db_init']);

            }

        } catch (PDOException $e) {
            if (!$options){
                self::fatalError ('Connection failed: ' . $e->getMessage());
            } else {
                if (isset($options['dont_die'])){
                    self::$debug[] = $e->getMessage();
                    self::$debug[] = 'No connection';
                    return "NO_DB_CONN";
                }
            }
        }
        self::$debug[]  = 'Connected!';
    }
    
    /**
     * checks if a field exists in a table
     * @param string $table the db table
     * @param string $field the table field
     * @return boolean $res true if the field exists false if not. 
     */
    public function fieldExists($table, $field) {
        $sql = "SHOW COLUMNS FROM `$table` LIKE '$field'";

        $rows = $this->selectQuery($sql);
        if (!empty($rows)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Method for selecting one row for a table. 
     *
     * @param string the tablename to select from (e.g. auth)
     * @param string fieldname the field to search from (e.g username)
     * @param string simple search conditions for the fieldname (e.g. admin) 
     *        'select * from auth where username = admin'
     * @param array fields the fields to select else * 
     * @return array the fetched row, emty row if no rows matched the search
     */
    public function selectOne($table, $fieldname=null, $search=null, $fields=null){
        $rows = $this->select($table, $fieldname, $search, $fields);
        foreach ($rows as $row){
            if (!empty($row)){
                return $row;
            } else {
                return array();
            }
        }
        return array();
    }
    
    /**
     * method for creating a mysql database
     * @param string $db
     * @return boolean $res 
     */
    public function createDB ($db) {
        $sql = '';
        $sql.= "CREATE DATABASE IF NOT EXISTS  `$db` ";
        $sql.= "DEFAULT CHARACTER SET utf8";
        $stmt = $this->rawQuery($sql);
        return $stmt->execute();
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
     * Method for deleting from a database table. If $fieldname is 
     * a string e.g. id then $search will be used to find which row to delete 
     * e.g. '3' which should be set in $search. If $search is an array you can 
     * add more conditions, e.g. array ('id' => 3, 'title' => 'test');
     *
     * @param   string  $table the database table to delete from .e.g. auth
     * @param   mixed   $fieldname the where clause e.g. where 'id' =
     * @param   mixed   $search sets a simple search option. e.g. '3'. It can 
     *                  also be an array like this: array ('id' => 3) 
     *                  delete from 'auth' Where id = 3
     * @return  boolean true on succes or false on failure
     */
    public function delete($table, $fieldname, $search){
        $sql = "DELETE FROM `$table` WHERE ";

        if (is_array($search)){
            foreach ($search as $key => $val){
                $params[] ="`$key`= " . self::$dbh->quote($val);;
            }
            $params = implode(' AND ', $params);
            $sql .= $params;
        } else {
            $search = self::$dbh->quote($search);
            $sql .= " `$fieldname` = $search";
        }

        self::$debug[]  = "Trying to prepare update sql: $sql";
        $stmt = self::$dbh->prepare($sql);

        if (is_array($search)){
            $ret = $stmt->execute($search);
        } else {
            $ret = $stmt->execute();
        }
        return $ret;
    }

    /**
     * Method for seleting all with the options for adding a limit and a order
     *
     * @param   string      $table the table were we want to select all from
     * @param   mixed       $fields to select (string or array of fields)
     *                      if null we the select will be all fields (*)
     * @param   array       $search array with simple search options e.g. 
     *                      array ('username' => 'admin', 'email' => 'dennis@coscms.org')
     * @param   int         $from from where in the resultset e.g. 200
     * @param   int         $limit max rows to fetch e.g. 10
     * @param   string      $order_by the field to order by
     * @param   asc         $asc ASC if true DESC if false
     * @return  array       $rows ASSOC array containing the selected row or false
     */
    public function selectAll($table, $fields = null, $search = null, $from = null,
                              $limit = null, $order_by = null, $asc = null){
            if ($fields){
                if (is_array($fields)) {
                    $fields = implode(' ,', $fields);
                    $sql = "SELECT " . $fields . " FROM `$table`";
                } else if (is_string($fields)) {
                    $sql = "SELECT " . $fields . " FROM `$table`";
                }
                
            } else {
                $sql = "SELECT * FROM `$table` ";
            }

            $sql .= " WHERE ";
            if (is_array($search) && !empty($search)){
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
                $sql.= " ORDER BY `$order_by` ";
                if ($asc == 1){
                    $sql.= "ASC ";
                } else {
                    $sql.= "DESC ";
                }

            }

            if (isset($from)){
                $from = (int)$from;
                $limit = (int)$limit;
                $sql.= "LIMIT $from, $limit";
            }
            self::$debug[]  = "Trying to prepare selectAll sql: $sql";
            try {     
                $stmt = self::$dbh->prepare($sql);
                if (is_array($search) && !empty($search)){
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
     * method for doing a insert or update. If search conditions finds 
     * a row, then this row is updated. If no row is found we insert a new
     * one. 
     * @param string $table
     * @param array $values
     * @param array $search e.g. array ('user_id' => 123);
     * @return type
     */
    public function replace ($table, $values, $search) {
        $row = $this->getNumRows($table, $search);
        if (!$row){
            $res = $this->insert('users', $values);
        } else {
            $res = $this->update('users', $values, $search);
        }
        return $res;
    }
    
    /**
     * Method for inserting values into a table
     *
     * @param   string  $table table the table to insert into
     * @param   array   $values to insert e.g. 
     *                  array ('username' => 'test', 
     *                         'password' => md5('test'))
     * @param   array   $bind if we want to bind any values specify 
     *                  the field and type in assoc array. 
     *                  array ('username' => PDO::STRING)
     * @return  boolean $res true on success or false on failure
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
     * @param   string  $table the table to search e.g. 'article'
     * @param   string  $match what to match, e.g 'title, content'
     * @param   string  $search what to select e.g. '*'
     * @param   string  $select what to search for e.g 'some search words'
     * @param   int     $from where to start getting the results
     * @param   int     $limit how many results to fetch e.g. 20
     * @return  array   $rows array of rows
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
     * @param   string  $table the table to search e.g. 'article'
     * @param   string  $match what to match, e.g 'title, content'
     * @param   string  $search what to search for e.g 'some search words'
     * @return  int     $num rows of search results from used full-text search
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
     * 
     * @param   string  $table the table to update
     * @param   array   $values which should be updated e.g.:
     *                  array ('username' => 'test', 'password' => md5('test')
     * @param   mixed   $search primary id of row (need to have an id in table or
     *                  array ('username' => 'test')
     * @param   array   $bind array with values and type to bind, e.g. 
     *                  array ('id' => PDO::INT)
     * @return  boolean $res true on success and false on failure
     */
    public function update($table, $values, $search, $bind = null){
        $sql = "Update `$table` SET ";

        foreach ($values as $field => $value ){
            $ary[] = " `$field`=" . ":$field ";
        }
        
        $sql.= implode (',', $ary);
        $sql.= " WHERE ";

        if (is_array($search)){
            foreach ($search as $key => $val){
                //array('username' => 1, $key => '2343');
                $params[] ="`$key`= " . self::$dbh->quote($val);
            }
            $params = implode(' AND ', $params);
            $sql .= $params;
        } else {
            $search = self::$dbh->quote($search);
            $sql.= " `id` = $search";
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
     * @param   string  $table to count number of rows in
     * @param   array   $where ('username => 'test')
     * @return  int     $num_rows number of rows
     */
    public function getNumRows($table, $where = null){
        if (!isset($where)) $where = array();
        $sql = "SELECT count(*) as num_rows FROM `$table`";
        if (!empty($where) && is_array($where)){
            $sql.= " WHERE ";
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
     * @return  mixed   $rows array the rows found. Or false on failure. 
     *
     */
    public function selectQuery($sql){
        self::$debug[]  = "Trying to prepare selectQuery sql: $sql";
        $stmt = self::$dbh->query($sql);
        $ret = $stmt->execute();
        if (!$ret) return false;
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }
    
        /**
     * Method for performing a direct selectQuery, e.g. if we are joinging rows
     *
     * @param   string  The query to execute
     * @return  mixed   $rows array the rows found. Or false on failure. 
     *
     */
    public function selectQueryOne($sql){
        $rows = $this->selectQuery($sql);
        if (isset($rows[0])) return $rows[0];
        return array ();
    }

    /**
     * Method for doing a raw query. Anything will go
     *
     * @param   string  $sql to query
     * @return  object  $stmt the object returned from the query
     */
    public static function rawQuery($sql){
        self::$debug[]  = "Trying to prepare rawQuery sql: $sql";
        $stmt = self::$dbh->query($sql);
        return $stmt;
    }

    /**
     * Method for preparing raw $_POST for execution. It just 
     * removes some common fields from post like e.g. 'submit', 'submitted',
     * MAX_FILE_SIZE, *method*, and captcha. 
     * @param array $values the values to prepare
     * @param array $options none so far
     * @return array  $values to use in update and insert sql commands.
     */
    public static function prepareToPost($values = array(), $options = array ()){
        self::$debug[] = "Trying to prepareToPost";
        if (!empty($values)) {
            self::prepareToPostArray();
        }
        
        $ary = array();
        foreach ($_POST as $key => $value){
            // continue if field value is 'submit' or 'captcha'
            if ($key == 'submit') continue;
            if ($key == 'prg_time') continue;
            if ($key == 'submitted') continue;
            if ($key == 'password2') continue;
            if ($key == 'captcha') continue;
            if ($key == 'MAX_FILE_SIZE') continue;
            if ($key == 'APC_UPLOAD_PROGRESS') continue;
            if (strstr($key, 'method')) continue;
            if (strstr($key, 'ignore')) continue;
                        
            $ary[$key] = $value;
        }
        return $ary;
    }
    
    /**
     * prepares an array for db post where we specify keys to use 
     * @param array $keys keys to use from request
     * @return array $ary array with post array we will use 
     */
    public static function prepareToPostArray ($keys, $null_values = true) {
        $ary = array ();
        foreach ($keys as $val) {
            if (isset($_POST[$val])) {
                $ary[$val] = $_POST[$val];
            } else {
                if ($null_values) {
                    $ary[$val] = NULL;
                }
            }
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
     * @param   string  $msg the message to show with the backtrace
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
