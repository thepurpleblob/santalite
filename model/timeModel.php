<?php

namespace model;

use core\coreModel;

class timeModel extends coreModel {

    function getAllTimes() {
        $sql = "SELECT * FROM traintime ORDER BY time";
        return $this->Query($sql);
    }
    
    function getTime($timeid) {
        $sql = "SELECT * FROM traintime WHERE id=$timeid";
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
