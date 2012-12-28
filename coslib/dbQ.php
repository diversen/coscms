<?php

/**
 * contains dbQ class fro creating db queries fairly simply
 * @package dbQ
 */

/**
 * class containg a simpler way for doing more complex queries (yet simple)
 * you may use the alternative name dbQ
 * 
 * @package dbQ
 */
class dbQ  {
    /**
     * holder for query being built
     * @var string $query holding query 
     */
    public static $query = null;

    /**
     * var holding PDO statement
     * @var object $stmt 
     */
    public static $stmt = null;

    /**
     * holding all statements that will be bound
     * @var array $bind.
     */
    public static $bind = array();

    /**
     * indicate if a WHERE sql sentence has been used
     * @var string|null  $where 
     */
    public static $where = null;
    
    /**
     * object holding db handle
     * @var object $dbh
     */
    public static $dbh = null;
    
    /**
     * array holding debug messages
     * @var array $debug
     */
    public static $debug = array();
    
    /**
     * var holding method (SELECT, UPDATE, INSERT, DELETE) 
     * @var type $method 
     */
    public static $method = '';
    
    /**
     * flag indicating if a sql method has been set
     * var $isset
     */
    public static $isset = null;

    /**
     * constructor inits object
     * @param array $options 
     */
    function __construct($options = null) {
        self::init($options);       
    }
    
    /**
     * inits object
     * @param array $options 
     */
    public static function init($options = null) {
        static $db = null;
        if (!isset($db)) {
            $db = new db();
            self::$dbh = db::$dbh;    
        } 
    }
    
    /**
     * 
     * @return string $debug
     */
    public static function getLastDebug () {
        return $debug = array_pop(self::$debug);
    }
    

    /**
     * escapes fields to select, e.g. 'id, date, test'
     * @param string $fields string of fields
     * @return string  escaped string of fields
     */
    public static function escapeFields ($fields) {
        $fields = explode(',', $fields);
        $ary = array ();
        foreach ($fields as $field) {
            $field = trim($field);
            $ary[] = " $field "; 
        }
        return implode(",", $ary);
    }
    
    /**
     * prepare for a select statement. 
     * 
     * @param string $table the table to select from 
     * @param string $fields the fields from the table to select 
     *             e.g. * or 'id, title'
     */
    
    public static function setSelect ($table, $fields = null){
        self::$method = 'select';
        
        if (!$fields) {
            $fields = '*';
        } else {
            $fields = self::escapeFields($fields);
        }
        
        self::$query = "SELECT $fields FROM `$table` ";
        return new dbQ;
    }
        
    /**
     * sets select statement for numrows
     * @param type $table
     * @return \dbQ 
     */
    public static function setSelectNumRows ($table){
        self::$method = 'num_rows';
        self::$query = "SELECT count(*) as num_rows FROM $table ";
        return new dbQ;
    }
    
    /**
     * prepare for a delete query
     * @param string $table the table to delete from
     */
    public static function setDelete ($table){
        self::$method == 'delete';
        self::$query = "DELETE FROM $table ";
        return new dbQ;
    }
    
    /**
     * prepare for an update query statement
     * @param type $table 
     */
    
    public static function setUpdate ($table) {
        self::$method = 'update';
        self::$query = "UPDATE $table SET ";
        return new dbQ;
    }
    
    /**
     * prepare for insert
     * @param type $table the table to insert values into
     */
    public static function setInsert ($table) {
        self::$method = 'INSERT';
        self::$query = "INSERT INTO $table ";
        return new dbQ;
    }
    
    /**
     * set values for insert or update. 
     * @param array $values the values to insert
     * @param array $bind array with types to bind values to
     */
    public static function setValues ($values, $bind = array()) {
        if (self::$method == 'update') {
            self::setUpdateValues($values, $bind);
        } else {
            self::setInsertValues($values, $bind);
        }
        return new dbQ;
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
        return new dbQ;
    } 
    
    /**
     * set insert values
     * @param array $values the values to insert into table
     * @param array $bind the values to bind values with
     */
    public static function setInsertValues ($values, $bind = array ()) {
        
        $fieldnames = array_keys($values);
        $fields = '( ' . implode(' ,', $fieldnames) . ' )';
        self::$query.= $fields . ' VALUES ';
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
        return new dbQ;
        
    } 

    /**
     * filter something in a query e.g. filter('id > 2', $value);
     * the values used will be prepared 
     * @param string $filter the filter to use e.g. 'id >
     * @param string $value the value to filter from e.g. '2'
     * @param string $bind  if we want to bind the value to a type. 
     */
    public static function filter ($filter, $value, $bind = null) {

        if (!self::$where) {
            self::$where = 1;
            self::$query.= "WHERE ";
        }

        self::$query.= " $filter ? ";
        self::$bind[] = array ('value' => $value, 'bind' => $bind);
        return new dbQ();
    }
    
    /**
     * filter for setting some additional sql
     * @param string $sql e.g. "id >= 3"
     */
    public static function sql ($sql) {

        if (!self::$where) {
            self::$where = 1;
            self::$query.= "WHERE ";
        }

        self::$query.= " $sql ";

        return new dbQ();
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
        return new dbQ();
    }

    /**
     * sets a condition between filters
     * @param string $condition (e.g. 'AND', 'OR')
     */
    public static function condition ($condition){
        self::$query.= " $condition ";
        return new dbQ;
    }

    /**
     * set ordering of the values which we tries to fetch
     * 
     * @param string $column column to order by, e.g. title
     * @param string $order (e.g. ASC or DESC)
     */
    public static function order ($column, $order = 'ASC', $options = array ()){
        
        if (!self::$isset) { 
            self::$query.= " ORDER BY $column $order ";
        } else {
            self::$query.= ", $column $order ";
        }   
        self::$isset = true;
        return new dbQ;
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
        return new dbQ;
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
        
        try {
            self::$debug[] = self::$query;
            self::init();
            self::$stmt = self::$dbh->prepare(self::$query);
            self::prepare();

            self::$stmt->execute();
            $rows = self::$stmt->fetchAll(PDO::FETCH_ASSOC);
            if (self::$method == 'select_one') {
                if (!empty($rows)) {
                    $rows = $rows[0];
                } 
            }
            

            self::unsetVars();
        } catch (Exception $e) {
            $message = $e->getTraceAsString();
            log::error($message);
            $last = self::getLastDebug();
            log::error($last);
            die();
            
        }
        if (self::$method == 'num_rows') {
            return $rows[0]['num_rows'];
        }       
        return $rows;
    }
    
    /**
     * method to execute a query, insert update or delte. 
     * @return boolean true on success and false on failure. 
     */
    public static function exec() {
        self::$debug[] = self::$query;    
        self::$stmt = self::$dbh->prepare(self::$query);
        try {
            self::prepare();       
            $res = self::$stmt->execute();
        } catch (Exception $e) {
            $message = $e->getTraceAsString();
            log::error($message);
            $last = self::getLastDebug();
            log::error($last);
            die;
            
        }
        self::unsetVars();
        return $res;
    }

    /**
     * fetch a single row, first in line
     * @return array $row single array
     */
    public static function fetchSingle (){
        self::limit(0, 1);
        $rows = self::fetch();
        if (isset($rows[0])){
            return $rows[0];
        }
        return array();
    }
    
    /**
     * method for unsetting static vars when an operation is compleate.
     */
    public static function unsetVars (){
        if (isset(config::$vars['coscms_main']['debug'])) {
            //cos_debug(self::$query);
        }
        self::$query = self::$isset = self::$bind = self::$where = self::$stmt = null;
    }
}
