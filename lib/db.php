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

    
    static public function init(){
        static $db = null;
        if (!$db) {
            $db = new db();
        } 
        return $db;
    }

    
    /**
     * constructor will try to call method connect
     */
    public function __construct($options = null){

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
     * @param array $options You can prevent the connection from halting on 
     *              error by setting $options['dont_die'] = true
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
        self::$debug[] = "Trying to connect with " . register::$vars['coscms_main']['url'];
        if (isset($options['url'])) {
            $url = $options['url'];
            $username = $options['username'];
            $password = $options['password'];
        } else {
            $url = get_main_ini('url');
            $username = get_main_ini('username');
            $password = get_main_ini('password');
        }
        
        try {
            self::$dbh = new PDO(
                get_main_ini('url'),
                get_main_ini('username'),
                get_main_ini('password')
            );
            self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if(!self::$dbh) $this->connect();
        } catch (PDOException $e) {
            if (!$options){
                self::fatalError ('Connection failed: ' . $e->getMessage());
            } else {
                if (isset($options['dont_die'])){
                    self::$debug[] = 'No connection';
                    return "NO_DB_CONN";
                }
            }
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
     * @param   string  $select what to select e.g. '*'
     * @param   string  $search what to search for e.g 'some search words'
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
     * @return  boolena true on success and false on failure
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
                $params[] ="`$key`= " . self::$dbh->quote($val);
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
     * Method for doing a raw query. Anything will go
     *
     * @param   string  $sql to query
     * @return  object  $stmt the object returned from the query
     */
    public function rawQuery($sql){
        self::$debug[]  = "Trying to prepare rawQuery sql: $sql";
        $stmt = self::$dbh->query($sql);
        return $stmt;
    }

    /**
     * Method for preparing raw $_POST for execution. It just 
     * removes some common fields from post like e.g. 'submit', 'submitted',
     * MAX_FILE_SIZE, *method*, and captcha. 

     *
     * @return  array   $values to use in update and insert sql commands.
     */
    static public function prepareToPost(){
        self::$debug[] = "Trying to prepareToPost";
        $ary = array();
        foreach ($_POST as $key => $value){
            // continue if field value is 'submit' or 'captcha'
            if ($key == 'submit') continue;
            if ($key == 'submitted') continue;
            if ($key == 'captcha') continue;
            if ($key == 'MAX_FILE_SIZE') continue;
            
            // remove hidden elements with a method_
            if (strstr($key, 'method')) continue;
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

/**
 * class containg a simpler way for doing more complex queries (yet simple)
 * you may use the alternative name dbQ
 * 
 * @package coslib 
 */

class QBuilder  {
    /**
     * holder for query being built
     * @var string $query holding query 
     */
    public static $query = null;

    /**
     * @var object $stmt holding PDO statement
     */
    public static $stmt = null;

    /**
     * @var array $bind. holding all statements that will be bound
     */
    public static $bind = array();

    /**
     *
     * @var string|null  $where indicatin if a WHERE sql sentence has been used
     */
    public static $where = null;
    
    /**
     * 
     * @var type $dbh object holding db handle
     */
    public static $dbh = null;
    
    /**
     * @var type $debug array for holding debug messages
     */
    public static $debug = array();
    
    /**
     *
     * @var type $method inidcation if we are updating or inserting values. 
     */
    public static $method = null;

    

    function __construct($options = null) {
        self::init($options);
        
    }
    
    public static function init($options = null) {
        static $db = null;
        if (!isset($db)) {
            $db = new db();
            self::$dbh = db::$dbh;    
        } 
    }
    
    /**
     * prepare for a select statement. 
     * 
     * @param string $table the table to select from 
     * @param string $fields the fields from the table to select 
     *             e.g. * or 'id, title'
     */

    public static function setSelect ($table, $fields ='*'){
        self::$query = "SELECT $fields FROM `$table` ";
    }
    
    /**
     * prepare for a delete query
     * @param string $table the table to delete from
     */
    public static function setDelete ($table){
        self::$query = "DELETE FROM `$table` ";
    }
    
    /**
     * prepare for an update query statement
     * @param type $table 
     */
    
    public static function setUpdate ($table) {
        self::$method = 'UPDATE';
        self::$query = "UPDATE `$table` SET ";
    }
    
    /**
     * prepare for insert
     * @param type $table the table to insert values into
     */
    public static function setInsert ($table) {
        self::$method = 'INSERT';
        self::$query = "INSERT INTO `$table`";
    }
    
    /**
     * set values for insert or update. 
     * @param array $values the values to insert
     * @param array $bind array with types to bind values to
     */
    public static function setValues ($values, $bind = array()) {
        if (self::$method == 'UPDATE') {
            self::setUpdateValues($values, $bind);
        } else {
            self::setInsertValues($values, $bind);
        }        
    }
    
    /**
     * prepare for update values
     * @param array $values the values to update with
     * @param array $bind the array with types to bind with 
     */
    public static function setUpdateValues ($values, $bind = array ()) {
        $ary = array();
        foreach ($values as $field => $value ){
            $ary[] = " `$field` =" . " ? ";
            if (isset($bind[$field])) {
                self::$bind[] = array ('value' => $value, 'bind' => $bind[$field]);
            } else {
                self::$bind[] = array ('value' => $value, 'bind' => null);
            }
        }
        
        self::$query.=  implode (',', $ary);
    } 
    
    /**
     * set insert values
     * @param array $values the values to insert into table
     * @param array $bind the values to bind values with
     */
    public static function setInsertValues ($values, $bind = array ()) {
        
        $fieldnames = array_keys($values);
        $fields = '( ' . implode(' ,', $fieldnames) . ' )';
        //$bound = '(:' . implode(', :', $fieldnames) . ' )';
        self::$query.= $fields . ' VALUES '; // . $bound;
        foreach ($fieldnames as $val) {
            $rest[] = '?';
        }
        
        self::$query.= '(' . implode(', ', $rest) . ')';
        
        foreach ($values as $field => $value ){
            
            if (isset($bind[$field])) {
                self::$bind[] = array ('value' => $value, 'bind' => $bind[$field]);
            } else {
                self::$bind[] = array ('value' => $value, 'bind' => null);
            }
        }
        
    } 

    /**
     * filter for something in a query e.g. filter('id > 2', $value);
     * the values used will be prepared by class. 
     * @param string $filter the filter to use e.g. 'id >
     * @param string $value the value to filter from e.g. '2'
     * @param string $bind  if we want to bind the value to a type. 
     */
    public static function filter ($filter, $value, $bind = null) {
        // e.g. id > 3
        static $where = null;

        if (!self::$where) {
            self::$where = 1;
            self::$query.= "WHERE ";
        }

        self::$query.= " $filter ? ";
        self::$bind[] = array ('value' => $value, 'bind' => $bind);
    }

    /**
     * filter for creating IN queries where we use an array of values
     * to create our filter from. 
     * @param string $filter waht to filter from, e.g. "ID in"
     * @param array $values the values which we will use, e.g. array(1, 2, 3) 
     */
    public static function filterIn ($filter, $values) {

        if (!self::$where) {
            self::$where = 1;
            self::$query.= "WHERE ";
        }

        self::$query.= " $filter ";
        self::$query.= "(";
        $num_val = count($values);

        foreach ($values as $key => $val){
            self::$query.=" ? ";
            self::$bind[] = array ('value' => $val, 'bind' => null);
            $num_val--;
            if ($num_val) self::$query.=",";
        }
        self::$query.=")";
    }

    /**
     * sets a condition between filters
     * @param string $condition (e.g. 'AND', 'OR')
     */
    public static function condition ($condition){
        self::$query.= " $condition ";
    }

    /**
     * set ordering of the values which we tries to fetch
     * 
     * @param string $column column to order by, e.g. title
     * @param string $order (e.g. ASC or DESC)
     */
    public static function order ($column, $order = 'ASC'){
        self::$query.= " ORDER BY `$column` $order ";
    }

    /**
     * method for setting a limit in the query
     * 
     * @param int $from where to start the limit e.g. 200
     * @param int $limit the limit e.g. 10
     */
    public static function limit ($from, $limit){
        $from = (int)$from;
        $limit = (int)$limit;
        self::$query.= " LIMIT $from, $limit";
    }

    /**
     * method for preparing all bound columns and corresponding values
     */
    public static function prepare (){
        if (self::$bind){
            $i = 1;
            foreach (self::$bind as $key => $val){
                self::$stmt->bindValue ($i, $val['value'], $val['bind']);
                $i++;
            }
        }
        self::$bind = null;

    }

    /**
     * method for fetching rows which we created with our query
     * @return array $rows assoc array of rows
     */
    public static function fetch (){
        self::init();
        self::$stmt = self::$dbh->prepare(self::$query);
        self::prepare();

        self::$stmt->execute();
        $rows = self::$stmt->fetchAll(PDO::FETCH_ASSOC);

        self::$debug[] = self::$query;
        self::unsetVars();
        self::$query = null;
        return $rows;
    }
    
    /**
     * method to execute a query, insert update or delte. 
     * @return boolean true on success and false on failure. 
     */
    public static function exec() {
        self::init();
        self::$debug[] = self::$query;    
        self::$stmt = self::$dbh->prepare(self::$query);
        self::prepare();       
        $res = self::$stmt->execute();
        self::unsetVars();
        return $res;
    }

    /**
     * fetch a single row, first in line
     * @return array $row single array
     */
    public static function fetchSingle (){
        $rows = self::fetch();
        if (isset($rows[0])){
            return $rows[0];
        }
        return array();
    }
    
    public static function numRows ($table) {
        $db = new dbQ();
        $db->setSelect($table, 'count(*) as numrows');
        $row = $db->fetchSingle();
        return $row['numrows'];
    }

    /**
     * method for unsetting static vars when an operation is compleate.
     */
    public static function unsetVars (){
        self::$query = self::$bind = self::$where = self::$stmt = null;
    }
}
/**
 * @package coslib
 * just a better name for QBuilder
 */
class dbQ extends QBuilder {
    
}
