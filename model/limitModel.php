<?php

namespace model;

use core\coreModel;

class limitModel extends coreModel {

    function getAllLimits() {
        $sql = "SELECT * FROM trainlimit ORDER BY time";
        return $this->Query($sql);
    }
    
    function getLimit($limitid) {
        $sql = "SELECT * FROM trainlimit WHERE id=$limitid";
        return $this->Query($sql, QUERY_SINGLE);
    }
    
    function getLimitByTime($dateid, $timeid) {
        $sql = "SELECT * FROM trainlimit WERE dateid=$dateid AND timeid=$timeid";
        return $this->Query($sql, QUERY_SINGLE);
    }
    
    function updateTime($time) {
        if ($time->id) {
            $sql = "UPDATE traintime SET time='{$time->time}' WHERE id={$time->id}";
            $this->Exec($sql);
        } else {        
            $sql = "INSERT INTO traintime (time) VALUES ({$time->time})";
            $this->Exec($sql);
        }
    }
   
}
