<?php

/**
 * @package coslib
 */

/**
 * @ignore for now.
 */

/**
 * @package coslib
 */
class db2 {

    static $query = '';
    static $dbh = false;
    static $debug = array();
    static $whereIsset = null;
    static $crudMethod = null;

    /**
     * Selects all rows from a table
     * @param  string $table
     * @return array $rows
     */
    public function selectAll($table, $search = array()){
        $this->method('SELECT');
        $this->fields('*');
        $this->table($table);
        $rows = $this->preExeSelect($search);
        return $rows;
    }

    /**
     * Select one row
     *
     * @param String $table
     * @param String $search
     * @return array $row
     */
    public function selectOne($table, $search){
        $this->method('SELECT');
        $this->fields('*');
        $this->table($table);
        $this->where($search);
        $this->limit(0, 1);
        $row = $this->preExeSelect($search);
        if (!empty($row)){
            return $row[0];
        } else {
            return array();
        }
    }

    /**
     * constructor checks if there is a handle otherwise connect and
     * creates a handle
     *
     * @param string $url
     * @param string $user
     * @param string $pass
     */
    function __construct($url = null, $user = null, $pass = null){
        if (!self::$dbh){
            if (!$url){
                self::connect(
                    register::$vars['coscms_main']['url'],
                    register::$vars['coscms_main']['username'],
                    register::$vars['coscms_main']['password']
                );
            }
        }
        return $this;
    }

    /**
     * Try to connect to database with specified params
     *
     * @param String $url
     * @param String $user
     * @param String $pass
     */
    public static function connect($url = null, $user = null, $pass = null){
        try {
            if (isset($user) && isset($pass)){
                self::$dbh = new PDO(
                    $url,
                    $user,
                    $pass
                );
            } else {
                self::$dbh = new PDO(
                    $url
                );
            }

            self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if(!self::$dbh) $this->connect();
        } catch (PDOException $e) {
            die ('Connection failed: ' . $e->getMessage() . "\n");
        }
    }

    /**
     * Sets a Crud method (insert, update, delete, read)
     * @param  String $method
     * @return Object $this
     */
    public function method ($method){
        $method = strtoupper($method);
        self::$crudMethod = $method;
        switch ($method) {
            case "INSERT": 
                self::$query = "INSERT INTO ";
                break;
            case "UPDATE": 
                self::$query = "UPDATE ";
                break;
            case "DELETE": 
                self::$query = "DELETE FROM ";
                break;
            case "SELECT": 
                self::$query = "SELECT ";
                break;
        }
        return $this;
    }

    /**
     * Sets field part of a query
     *
     * @param String $fields
     * @return object $this
     */
    public function fields ($fields){
        self::$query.= " $fields FROM ";
        return $this;
    }

    /**
     * Sets table part of query
     * @param  String $table
     * @return object $this
     */
    public function table($table){
        self::$query.= $table . " ";
        return $this;
    }

    /**
     * Sets where part part of query
     *
     * @param  array  $search
     * @param  String $logic
     * @return object $this
     */
    public function where ($search = array(), $logic = 'AND'){
        if (!self::$whereIsset){
            self::$query.= "WHERE ";
            self::$whereIsset = true;
        }
        foreach ($search as $key => $val){
            $params[] ="$key = :$key";
        }
        $params = implode(" $logic ", $params);
        self::$query.= $params;
        return $this;
    }

    /**
     * Sets order by part of a query
     * @param  string  $orderBy
     * @return object  $this
     */
    public function orderBy ($orderBy) {
        self::$query.= " ORDER BY " . $orderBy;
        return $this;
    }

    /**
     * Sets limit part of aa query
     * @param  int    $from
     * @param  int    $limit
     * @return object $this
     */
    public function limit ($from, $limit) {
        self::$query.= " LIMIT $from, $limit";
        return $this;
    }

    /**
     * Prepares and executes a select query
     *
     * @param  array  $search (values to be prepared and executed)
     * @return array  $rows
     */
    public function preExeSelect($search = array()){
        $stmt = self::$dbh->prepare(self::$query);
        foreach ($search as $key => $val){
            $stmt->bindValue (":$key", $val);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows;
    }

    /**
     * prepares and executes a insert query
     *
     * @param  array     $values
     * @param  array     $bind
     * @return boolean   true on success and false on failure
     */
    public function preExeInsert($values, $bind = null){
        $fieldnames = array_keys($values);
        $fields = '( ' . implode(' ,', $fieldnames) . ' )';
        $bound = '(:' . implode(', :', $fieldnames) . ' )';
        self::$query.= $fields.' VALUES '.$bound;
        $stmt = self::$dbh->prepare(self::$query);
        if (isset($bind) && is_array($bind)){
            foreach ($values as $key => $val){
                if (isset($bind[$key])){
                    $stmt->bindParam(":".$key, $values[$key], $bind[$key]);
                } else {
                    $stmt->bindParam(":".$key, $values[$key]);
                }
            }
            return  $stmt->execute();
        }
        return $stmt->execute($values);
    }

    /**
     * wrapper function for doing a insert
     * @param String $table
     * @param array  $values
     * @param array  $bind
     * @return boolean
     */
    public function insert ($table, $values, $bind = null){
        $this->method('insert');
        $this->table($table);
        $ret = $this->preExeInsert($values);
        return $ret;
    }

    /**
     * prepares and executes an update

     * @param  array $values
     * @param  array $bind
     * @param  array $search ('id' => 3)
     * @return boolean
     */
    public function preExeUpdate($values, $search, $bind = null ){
        $fieldnames = array_keys($values);
        self::$query.= " SET ";

        foreach ($values as $field => $value ){
            $ary[] = " $field=" . ":$field ";
        }

        self::$query.= implode (',', $ary);
        self::$query.= " WHERE ";
        
        foreach ($search as $key => $val){
            self::$query.=" $key = " . self::$dbh->quote($val);
        }
        
        $stmt = self::$dbh->prepare(self::$query);
        if (isset($bind) && is_array($bind)){
            foreach ($values as $key => $val){
                if (isset($bind[$key])){
                    $stmt->bindParam(":".$key, $values[$key], $bind[$key]);
                } else {
                    $stmt->bindParam(":".$key, $values[$key]);
                }
            }
            return $stmt->execute();
        } 
        return $stmt->execute($values);
    }

    /**
     * wrapper function for doing a insert
     * @param String $table
     * @param array  $values
     * @param array  $bind
     * @return boolean
     */
    public function update ($table, $values, $search, $bind = null){
        $this->method('update');
        $this->table($table);
        $ret = $this->preExeUpdate($values, $search, $bind);
        return $ret;
    }

    /**
     * Executes a delete statement
     * @param  array  $search
     * @param  string $logic
     * @return boolean
     */
    public function preExeDelete($search, $logic = 'AND'){
        foreach ($search as $key => $val){
            $params[] ="$key = :$key";
        }
        $params = implode(" $logic ", $params);
        self::$query.= "WHERE " . $params;
        $stmt = self::$dbh->prepare(self::$query);
        foreach ($search as $key => $val){
            $stmt->bindValue (":$key", $val);
        }
        $ret = $stmt->execute();     
        return $ret;
    }

/**
     * wrapper function for doing a insert
     * @param String $table
     * @param array  $values
     * @param array  $bind
     * @return boolean
     */
    public function delete ($table, $search){
        $this->method('delete');
        $this->table($table);
        $ret = $this->preExeDelete($search);
        return $ret;
    }

    /**
     * raw select query for fetching rows
     * @param  String $sql
     * @return array  $rows
     */
    public function selectQuery($sql){
        $stmt = self::$dbh->query($sql);
        $ret = $stmt->execute();
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
     * method for preparing a form submission. wil remove
     * submit fields and cpatcha fields from array
     * @param   array $ary
     * @return  array $ret
     */
    public static function prepareToPost($ary = array()){
        self::$debug[] = "Trying to prepareToPost";
        $ret = array();
        foreach ($ary as $key => $value){
            // continue if field value is 'submit' or 'captcha'
            if ($key == 'submit') continue;
            if ($key == 'captcha') continue;
            $ret[$key] = $value;
        }
        return $ret;
    }

    /**
     * error method
     * @param String $msg
     */
    public static function fatalError($msg) {
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