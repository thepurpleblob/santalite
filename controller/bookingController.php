<?php

namespace controller;

use core\coreController;
use model\bookingModel;

class bookingController extends coreController {

    protected $bm;

    public function __construct() {
        parent::__construct();
        $this->bm = new bookingModel;
    }

    public function startAction() {
        $this->View('header');
        $this->View('booking_start');
        $this->View('footer');
    }

    public function dateAction() {
        $cal = $this->getLib('calendar');

        // get dates
        $dates = \ORM::for_table('traindate')->order_by_asc('date')->find_many();
        
        // work out what months we need to show
        $months = array();
        foreach ($dates as $date) {
            $month_number = date('m', $date->date);
            $months[$month_number] = $month_number;
        }

        // build calendars
        $calendar = '';
        foreach ($months as $month) {
            $calendar .= $cal->showMonth($month, 2014);           
        }

        $this->View('header');
        $this->View('booking_date', array(
            'calendar' => $calendar,
        ));
        $this->View('footer');
    }

}
