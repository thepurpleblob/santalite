<?php
/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Model / library
 */

namespace thepurpleblob\santa\lib;

class santalib {

    protected $controller;

    public function _construct($controller) {
        $this->controller = $controller;
    }

    /**
     * Format dates
     * Displayable version of dates
     * @param array $dates
     * @param return array
     */
    public function format_dates($dates) {
        $fdates = [];
        $count = 0;
        foreach ($dates as $date) {
             $fdate = new \stdClass;
             $fdate->id = $date->id;
             $fdate->dateid = $date->id;
             $fdate->formatteddate = date('l d/m/Y', $date->date);
             $fdate->count = ++$count;
             $fdates[] = $fdate;
        }

        return $fdates;
    }

    /**
     * Format times
     * @param array $times
     * @return array
     */
    public function format_times($times) {
        $ftimes = [];
        $count = 0;
        foreach ($times as $time) {
            $ftime = new \stdClass;
            $ftime->id = $time->id;
            $ftime->timeid = $time->id;
            $ftime->time = $time->time;
            $ftime->count = ++$count;
            $ftimes[] = $ftime;
        }

        return $ftimes;
    }

    /**
     * Get suitable default date
     * Next Saturday or Sunday from current highest
     * @return int default date (unix time stamp)
     */
    public function get_default_date() {

        // Highest date stored
        $maxdate = \ORM::forTable('traindate')->max('date');
        if (!$maxdate) {
            $maxdate = strtotime('last day of October');
        }
        do {
            $maxdate += 86400;
            $day = date('l', $maxdate);
        } while (($day != 'Saturday') && ($day != 'Sunday'));

        return $maxdate;
    }

    /**
     * Format users for display
     * @param array $users
     * @return array
     */
    public function format_users($users) {
        $fusers = [];
        foreach ($users as $user) {
            $fuser = new \stdClass;
            $fuser->id = $user->id;
            $fuser->username = $user->username;
            $fuser->fullname = $user->fullname;
            $fuser->role = $user->role;
            $fuser->isadmin = $user->role == 'admin';
            $fusers[] = $fuser;
        }

        return $fusers;
    }

}
