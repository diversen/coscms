<?php

/**
 * Contains an extension for db 
 * @deprecated 
 * @package coslib 
 */

/**
 * @deprecated strage that db2 is deprecated but db is not. I used this to 
 * play around, but ended up making QBuilder which does the same: Makes easy
 * querys to db
 * @package coslib 
 * 
 */
class db2 {

   /**
     * @var false|object database handle
     */
    static $dbh = false;

    /**
     *
     * @var  array  holds all sql statements when in debug mode.
     */
    public static $debug = array();

    /**
     * @var for holding query
     */
    public static $query = null;

    /**
     * @var for holding PDO statement
     */
    public static $stmt = null;

    /**
     *
     * @var array   holding all statements that will be bound
     */
    public static $bind = array();

    /**
     *
     * @var string|null  indicatin if a WHERE sql sentence has been used
     */
    public static $where = null;

    /**
     * constructor will try to call method connect
     * if no database handle exists
     */
    public function __construct(){
        if (!self::$dbh){
            self::connect();
        }
    }

   public function connect($options = null){
        //global $_COS_MAIN;
        self::$debug[] = "Trying to connect with " . register::$vars['coscms_main']['url'];
        try {
            self::$dbh = new PDO(
                register::$vars['coscms_main']['url'],
                register::$vars['coscms_main']['username'],
                register::$vars['coscms_main']['password']

            );
            self::$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if(!self::$dbh) $this->connect();
        } catch (PDOException $e) {
            if (!$options){
                $this->fatalError ('Connection failed: ' . $e->getMessage());
            } else {
                if (isset($options['dont_die'])){
                    return "NO_DB_CONN";
                }
            }
        }
        self::$debug[]  = 'Connected!';
    }

    /**
     *
     * @param string $table the table to select from
     * @param string $columns the columns to fetch
     *               e.g. 'title, year, updated' else '*'
     */
    public static function get($table, $columns = '*'){
        self::$query = "SELECT $columns FROM $table ";
    }

    /**
     * method for counting num rows in a table
     * @param string $table
     * @return int   num rows in the table
     */
    public static function numRows($table){
        self::$query = "SELECT count(*) as num_rows FROM $table ";
        $stmt = self::$dbh->prepare(self::$query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $rows[0]['num_rows'];
    }

    /**
     *
     * @param string $colunm and condition e.g. "id >"
     * @param string $value '20' if id or e.g. 'title' if a title'
     * @param int    $bind PDO constant indicating which datatype we
     *               are using, e.g. 1 = INT, 2 => STRING
     */
    public static function filter ($column, $value, $bind = null){
        if (self::$where != 'set')
            self::$query.= " WHERE ";

        self::$where = 'set';

        self::$query.= " $column ? ";
        self::$bind[] = array ('value' => $value, 'bind' => $bind);
    }

    /**
     *
     * @param string $condition (e.g. 'AND' 'OR')
     */
    public static function condition ($condition){
        self::$query.= " $condition ";
    }

    /**
     *
     * @param string $column column to order by
     * @param string $order (ASC or DESC)
     */
    public static function order ($column, $order = 'ASC'){
        self::$query.= " ORDER BY $column $order";
    }

    /**
     * method for setting a limit in the query
     * @param int $from
     * @param int $limit
     */
    public static function limit ($from, $limit){
        self::$query.= " LIMIT $from, $limit";
    }

    /**
     * method for preparing all bound columns and corresponding values
     */
    private static function prepare (){
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
     * method for fetching rows from database
     * @return array    assoc array of rows
     */
    public static function fetch (){
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
     * method for unsetting static vars when an operation is compleate.
     */
    public static function unsetVars (){
        self::$query = self::$bind = self::$where = self::$stmt = null;
    }

    /**
     * shorthand for selecting all rows in a database
     * @param   string $table
     * @return  array  assoc array of all rows.
     */
    public static function select ($table){
        self::get($table);
        return self::fetch();
    }

    /**
     *
     * @param string $table
     * @param int    $id (optional) when set we presume that there is
     *               a column with the name 'id' in the table and
     *               delete the row with this id.
     * @return <type>
     */
    public static function delete ($table, $id = null) {
        self::$query = "DELETE FROM `$table` ";
        if ($id){
            self::$query = " WHERE id = ?";
            $stmt = self::$dbh->prepare($sql);
            $stmt->bindParam(1, $id);
            $ret = $stmt->execute();
            return $ret;
        }
    }

    /**
     *
     * @return boolean  $res result from update or delete action
     */
    public static function put (){
        self::$stmt = self::$dbh->prepare(self::$query);
        self::prepare();

        $res = self::$stmt->execute();

        self::$debug[] = self::$query;
        self::unsetVars();
        return $res;
    }

    /**
     * method for starting a update on a database table.
     * @param string $table
     * @param array  $values assoc array of values. Like
     *               array ('title' => 'new title', 'update' => '2011-02-02')
     */
    public static function update ($table, $values){
        self::$query = "Update `$table` SET ";
        $ary = array ();
        foreach ($values as $key => $value){
            $ary[] =  "$key = ? ";
            self::$bind[] = array ('value' => $value, 'bind' => null);
        }
        self::$query.= implode (',', $ary);
    }

    /**
     * Method for inserting values into a table
     *
     * @param   string  table the table to insert into
     * @param   array   values to insert, .e.g <code>array ('username' => 'test', 'password' => md5('test'))</code>
     * @return  boolean true or false
     */
    public static function insert($table, $values, $bind = null){
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
}