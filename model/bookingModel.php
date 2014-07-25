<?php

namespace model;

class bookingModel {
    
    /**
     * get operating months and days therein(numbers)
     * encode month/year, bit of a bodge
     */
    public function getMonthsDays($dates) {
        $months = array();
        foreach ($dates as $date) {
            $month = date('m/Y', $date->date);
            $day = date('j', $date->date);
            if (!isset($months[$month])) {
                $months[$month] = array();
            }
            $months[$month][$day] = $date->id;
        }
        
        return $months;
    }
    
}

