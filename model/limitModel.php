<?php

namespace model;

use core\coreModel;

class limitModel extends coreModel {

    public function getAllLimits() {
        $sql = "SELECT * FROM trainlimit ORDER BY time";
        return $this->Query($sql);
    }
    
    public function getLimit($limitid) {
        $sql = "SELECT * FROM trainlimit WHERE id=$limitid";
        return $this->Query($sql, QUERY_SINGLE);
    }
    
    public function getLimitByTime($dateid, $timeid) {
        $sql = "SELECT * FROM trainlimit WERE dateid=$dateid AND timeid=$timeid";
        return $this->Query($sql, QUERY_SINGLE);
    }
    
    public function getFormLimits($dates, $times) {
        global $CFG;
        
        $limits = array();
        foreach ($dates as $date) {
            foreach ($times as $time) {
            
                // fabricate an index based on date and time index
                $index = "{$date->id}_{$time->id}";
                
                // check if this entry is in the database
                $sql = "SELECT * FROM trainlimit WHERE dateid=$date->id AND timeid=$time->id";
                $limit = $this->Query($sql, QUERY_SINGLE);
                if (!$limit) {
                    $limit = new \stdClass;
                    $limit->timeid = $time->id;
                    $limit->dateid = $date->id;
                    $limit->maxlimit = $CFG->default_limit;
                    $limit->partysize = $CFG->default_party;
                    $sql = "INSERT INTO trainlimit (timeid, dateid, maxlimit, partysize)
                        VALUES ($time->id, $date->id, $CFG->default_limit, $CFG->default_party)";
                    $this->Exec($sql);
                }
                $limits[$date->id][$time->id] = $limit;
            }
        }

        return $limits;
    }
    
    public function saveForm($dates, $times, $request) {
        foreach ($dates as $date) {
            foreach ($times as $time) {
                $index = "{$date->id}_{$time->id}";
                if (!isset($request['limit'.$index])) {
                    throw new \Exception('Missing limit for '.$index);
                }   
                $limit = $request['limit'.$index];
                if (!isset($request['party'.$index])) {
                    throw new \Exception('Missing party size for '.$index);
                }
                $party = $request['party'.$index];
                $sql = "UPDATE trainlimit SET maxlimit=$limit, partysize=$party
                    WHERE timeid=$time->id AND dateid=$date->id";
                $this->Exec($sql);
            }
        }
    }
   
}
