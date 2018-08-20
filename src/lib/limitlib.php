<?php
/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Limits library
 */

namespace thepurpleblob\santa\lib;

class limitlib {
    
    public function getPassengerCount($limitid) {
        $filter = array(
                'trainlimitid' => $limitid,
                'status' => 'OK',
        );
        $sumadult = \ORM::for_table('purchase')->where($filter)->sum('adult');
        $sumchild = \ORM::for_table('purchase')->where($filter)->sum('child');
        return $sumadult + $sumchild;
    }
    
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
                $limit->count = $this->getPassengerCount($limit->id());
                $limit->formid = $date->id . '_' . $time->id;
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
    
    /**
     * Get trainlimit
     */
    private function getTrainlimit($dateid, $timeid) {
        $limit = \ORM::for_table('trainlimit')->where(array(
                'dateid' => $dateid,
                'timeid' => $timeid,
        ))->find_one();
        if (!$limit) {
            throw new \Exception("No limit record found in DB for timeid=".$time->id()." dateid=".$date->id());
        }
        return $limit;
    }
    
    public function getDetails($dateid, $timeid) {
        $date = \ORM::for_table('traindate')->find_one($dateid);
        if (!$date) {
            throw new \Exception('Date not found for dateid='.$dateid);
        }
        $time = \ORM::for_table('traintime')->find_one($timeid);
        if (!$time) {
            throw new \Exception('Time not found for timeid='.$timeid);
        }
        
        // start with date and time
        $details = new \stdClass();
        $details->date = date('l d/m/Y', $date->date);
        $details->time = $time;
        
        // create a filter and do some sums
        $limit = $this->getTrainlimit($dateid, $timeid);
        $filter = array(
                'trainlimitid' => $limit->id(),
                'status' => 'OK',
        );
        $details->sumadult = \ORM::for_table('purchase')->where($filter)->sum('adult');
        if (!$details->sumadult) {
            $details->sumadult = 0;
        }
        $details->sumchild = \ORM::for_table('purchase')->where($filter)->sum('child');
        if (!$details->sumchild) {
            $details->sumchild = 0;
        }
        $details->count = \ORM::for_table('purchase')->where($filter)->count();
        $details->limit = $limit->maxlimit;
        $details->total = $details->sumadult + $details->sumchild;
        $details->remaining = $details->limit - $details->total;
        
        return $details;
    }
   
}
