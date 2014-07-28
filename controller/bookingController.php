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
    	$bm = new bookingModel();

    	// need dateid from session
    	$dateid = $this->getFromSession('dateid');
    	$date = \ORM::for_table('traindate')->find_one($dateid);
    	if (!$date) {
    		throw new \Exception('Traindate not found in database for id='.$dateid);
    	}

    	// get available times
    	$times = \ORM::for_table('traintime')->order_by_asc('time')->find_many();

    	// check for submission
    	if ($timeid) {
    		$time = \ORM::for_table('traintime')->find_one($timeid);
    		if (!$time) {
    			throw new \Exception('Time not found in database id='.$timeid);
    		}

    		$_SESSION['timeid'] = $timeid;
    		$this->redirect($this->Url('booking/partysize'));
    	}

        $this->View('header');
        $this->View('booking_time', array(
            'date' => $date,
            'times' => $times,
        ));
        $this->View('footer');
    }

    public function partysizeAction() {
    	$bm = new bookingModel();

    	// get session data
    	$dateid = $this->getFromSession('dateid');
    	$timeid = $this->getFromSession('timeid');

    	echo "Date: $dateid   Time: $timeid";
    }

}
