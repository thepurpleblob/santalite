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
        $sumadult = \ORM::for_table('purchase')->where('trainlimitid', $limitid)->where_like('status', 'OK%')->sum('adult');
        $sumchild = \ORM::for_table('purchase')->where('trainlimitid', $limitid)->where_like('status', 'OK%')->sum('child');
        return $sumadult + $sumchild;
    }
    
    public function getFormLimits($dates, $times) {
        global $CFG;
        
        $limits = array();
        foreach ($dates as $date) {
            foreach ($times as $time) {
            
                // check if this entry is in the database
                $trainlimit = \ORM::for_table('trainlimit')->where(array(
                    'dateid' => $date->id,
                    'timeid' => $time->id,
                ))->find_one();
                if (!$trainlimit) {
                    $trainlimit = \ORM::for_table('trainlimit')->create();
                    $trainlimit->timeid = $time->id;
                    $trainlimit->dateid = $date->id;
                    $trainlimit->maxlimit = $CFG->default_limit;
                    $trainlimit->partysize = $CFG->default_party;
                    $trainlimit->save();
                }
                $limit = new \stdClass;
                $limit->trainlimit = $trainlimit;
                $limit->count = $this->getPassengerCount($trainlimit->id());
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
        $details->sumadult = \ORM::for_table('purchase')->where('trainlimitid', $limit->id())->where_like('status', 'OK%')->sum('adult');
        if (!$details->sumadult) {
            $details->sumadult = 0;
        }
        $details->sumchild = \ORM::for_table('purchase')->where('trainlimitid', $limit->id())->where_like('status', 'OK%')->sum('child');
        if (!$details->sumchild) {
            $details->sumchild = 0;
        }
        $details->count = \ORM::for_table('purchase')->where_like('status', 'OK%')->count();
        $details->limit = $limit->maxlimit;
        $details->total = $details->sumadult + $details->sumchild;
        $details->remaining = $details->limit - $details->total;
        
        return $details;
    }
   
}
