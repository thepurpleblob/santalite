<?php

namespace model;

use core\coreModel;

class dateModel extends coreModel {

    function getAllDates() {
        $sql = "SELECT * FROM traindate ORDER BY date";
        return $this->Query($sql);
    }
    
    function getDate($dateid) {
        $sql = "SELECT * FROM traindate WHERE id=$dateid";
        return $this->Query($sql, QUERY_SINGLE);
    }
    
    function updateDate($date) {
        if ($date->id) {
            $sql = "UPDATE traindate SET date='{$date->date}' WHERE id={$date->id}";
            $this->Exec($sql);
        } else {        
            $sql = "INSERT INTO traindate (date) VALUES ({$date->date})";
            $this->Exec($sql);
        }
    }
    
    function deleteDate($dateid) {
        $sql = "DELETE FROM traindate WHERE id=$dateid";
        $this->Exec($sql);
    }
}
