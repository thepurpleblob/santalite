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

    public function dateAction($dateid=0) {
        $cal = $this->getLib('calendar');
        $bm = new bookingModel();

        // process data
        if ($dateid) {
            $date = \ORM::for_table('traindate')->find_one($dateid);
            if (!$date) {
            	throw new Exception("Date id $dateid not found in database");
            }

            $_SESSION['dateid'] = $dateid;
            $this->redirect($this->Url('booking/time'));
        }

        // get dates
        $dates = \ORM::for_table('traindate')->order_by_asc('date')->find_many();
        $months = $bm->getMonthsDays($dates);

        // TODO: need to work out *available* dates from purchases

        // build calendars
        $calendar = '';
        foreach ($months as $month => $days) {
            list($month_number, $year) = explode('/', $month);
            $calendar .= $cal->showMonth($month_number, $year, $days, $this->Url('booking/date/'));
        }

        $this->View('header');
        $this->View('booking_date', array(
            'calendar' => $calendar,
        ));
        $this->View('footer');
    }

    public function timeAction($timeid=0) {
    	echo "got here";
    }

}
