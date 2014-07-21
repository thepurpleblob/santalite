<?php

namespace model;

class limitModel {
    
    public function getFormLimits($dates, $times) {
        global $CFG;
        
        $limits = array();
        foreach ($dates as $date) {
            foreach ($times as $time) {
            
                // check if this entry is in the database
                $limit = \ORM::for_table('trainlimit')->where(array(
                    'dateid' => $date->id,
                    'timeid' => $time->id,
                ))->find_one();
                if (!$limit) {
                    $limit = \ORM::for_table('trainlimit')->create();
                    $limit->timeid = $time->id;
                    $limit->dateid = $date->id;
                    $limit->maxlimit = $CFG->default_limit;
                    $limit->partysize = $CFG->default_party;
                    $limit->save();
                }
                $limits[$date->id][$time->id] = $limit;
            }
        }

        return $limits;
    }
    
    public function saveForm($dates, $times, $request) {
        foreach ($dates as $date) {
            foreach ($times as $time) {
                $limit = \ORM::for_table('trainlimit')->where(array(
                    'dateid' => $date->id,
                    'timeid' => $time->id,
                ))->find_one();
                $index = "{$date->id}_{$time->id}";
                if (!isset($request['limit'.$index])) {
                    throw new \Exception('Missing limit for '.$index);
                }   
                $limit->maxlimit = $request['limit'.$index];
                if (!isset($request['party'.$index])) {
                    throw new \Exception('Missing party size for '.$index);
                }
                $limit->partysize = $request['party'.$index];
                $limit->save();
            }
        }
    }
   
}
