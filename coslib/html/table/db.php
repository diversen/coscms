<?php

class html_table_db {
    
    
    public function getShowTable ($table) {
        $db = new db();
        $sql = "DESCRIBE `$table`";
        $rows = $db->selectQuery($sql);       
        return $rows;
        
    } 
    
    public function getTable($table, $from, $limit = 100) {
        $total = db_q::numRows('account')->fetch();
        $p = new pearPager($total);
        $rows = db_q::select($table)->limit($p->from, $limit)->fetch();
        $str = "<table border =1><tr>";
        $str.= $this->getTableHeaders($table);
        $str.= $this->getTableRows($rows);
        $str.= "</tr></table>";
        $str.= $p->getPagerHTML();
        return $str;
    }
    
    public function getTableHeaders($table) {
        $rows = $this->getShowTable($table);
        $str = '';
        foreach($rows as $row) {
            $str.= $this->getTd($row['Field'], true);
        }
        return $str;
    }
    
    public function getTableRows ($rows) {
        $str = '';
        foreach ($rows as $row) {
            $str.= "<tr>\n";
            foreach ($row as $single) {
                //print_r($single);
                
                $str.=$this->getTd($single);
                
            }
            $str.= "</tr>\n";
        }
        return $str;
    }
    
    public function getTd($str, $header = false) {
        if ($header) {
            $td = "<th>"; $td_end = "</th>";
        } else {
            $td = "<td>"; $td_end = "</td>";
        }
        return $td . $str. $td_end . "\n";
    }
    
    public function getTableAsHtmlTable ($table) {
        
        
        
    }
    
    
    
}