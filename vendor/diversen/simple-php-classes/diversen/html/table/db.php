<?php

namespace diversen\html\table;
use diversen\db\q as db_q;
use diversen\pagination as paginate;

/**
 * File containing class for building a simple table from db
 * @package html 
 */

/**
 * class containing methods for building a very simple table from db
 * @package html
 */

class db {
    
    /**
     * get table definition
     * @param string $table
     * @return array $rows
     */
    public function getShowTable ($table) {
        $db = new db();
        $sql = "DESCRIBE `$table`";
        $rows = $db->selectQuery($sql);       
        return $rows;        
    } 
    
    /**
     * get the table
     * @param string $table
     * @param int $from
     * @param int $limit
     * @return string $html
     */
    public function getTable($table, $from, $limit = 100) {
        $total = db_q::numRows('account')->fetch();
        $p = new paginate($total);
        $rows = db_q::select($table)->limit($p->from, $limit)->fetch();
        $str = "<table border =1><tr>";
        $str.= $this->getTableHeaders($table);
        $str.= $this->getTableRows($rows);
        $str.= "</tr></table>";
        $str.= $p->getPagerHTML();
        return $str;
    }
    
    /**
     * get table headers
     * @param string $table
     * @return string $html
     */
    protected function getTableHeaders($table) {
        $rows = $this->getShowTable($table);
        $str = '';
        foreach($rows as $row) {
            $str.= $this->getTd($row['Field'], true);
        }
        return $str;
    }
    
    /**
     * get all the table rows
     * @param array $rows
     * @return string $html
     */
    protected function getTableRows ($rows) {
        $str = '';
        foreach ($rows as $row) {
            $str.= "<tr>\n";
            foreach ($row as $single) {
                $str.=$this->getTd($single);
            }
            $str.= "</tr>\n";
        }
        return $str;
    }
    
    /**
     * get td data
     * @param string $str
     * @param boolean $bool display headers
     * @return string $html
     */
    public function getTd($str, $header = false) {
        if ($header) {
            $td = "<th>"; $td_end = "</th>";
        } else {
            $td = "<td>"; $td_end = "</td>";
        }
        return $td . $str. $td_end . "\n";
    }
}
