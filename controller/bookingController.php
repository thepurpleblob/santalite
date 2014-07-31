<?php

namespace controller;

use core\coreController;
use model\bookingModel;
use model\bookingRecord;

class bookingController extends coreController {

    protected $bm;

    private function fill($low, $high) {
    	$a = array();
    	for ($i=$low; $i<=$high; $i++) {
    		$a[$i] = $i;
    	}
    	return $a;
    }

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
        $br = new bookingRecord();

        // process data
        if ($dateid) {
            $date = \ORM::for_table('traindate')->find_one($dateid);
            if (!$date) {
            	throw new Exception("Date id $dateid not found in database");
            }

            $br->setDateid($dateid);
            $br->save();
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
    	$br = new bookingRecord();

    	// need dateid from session
    	$dateid = $br->getDateid();
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

    		$br->setTimeid($timeid);
    		$br->save();
    		$this->redirect($this->Url('booking/numbers'));
    	}

        $this->View('header');
        $this->View('booking_time', array(
            'date' => $date,
            'times' => $times,
        ));
        $this->View('footer');
    }

    public function numbersAction() {
    	$bm = new bookingModel();
    	$br = new bookingRecord();
    	$gump = $this->getGump();

    	// get session data
    	$dateid = $br->getDateid();
    	$timeid = $br->getTimeid();

        // get stuff from database
        $date = \ORM::for_table('traindate')->find_one($dateid);
    	if (!$date) {
    		throw new \Exception('Traindate not found in database for id='.$dateid);
    	}
        $time = \ORM::for_table('traintime')->find_one($timeid);
    	if (!$time) {
    		throw new \Exception('Traintime not found in database for id='.$timeid);
    	}

        // get limits for above
        $limit = \ORM::for_table('trainlimit')->where(array(
        	'dateid' => $dateid,
            'timeid' => $timeid,
            ))->find_one();
        if (!$limit) {
        	throw new \Exception('No limit found for timeid='.$timeid.', dateid='.$dateid);
        }


        // add validator for maximum partysize
        \GUMP::add_validator("partysize", function($field, $input, $param=null) {
        	$total = $input['adults'] + $input['children'] + $input['infants'];
        	return $total<=$param;
        });

        // get fares
        $fares = \ORM::for_table('fares')->find_one(1);

        // choices
        $adultchoices = $this->fill(1, $limit->partysize);
        $childrenchoices = $this->fill(0, $limit->partysize);
        $childrenchoices[0] = 'None';
        $infantchoices = $this->fill(0, $limit->partysize);
        $infantchoices[0] = 'None';

        // form submitted?
        if ($request = $this->getRequest()) {
        	if (!empty($request['cancel'])) {
        		$this->redirect($this->Url('booking/time'));
        	}

        	$lim = $limit->partysize;
        	$gump->validation_rules(array(
        		'adults' => "required|numeric|min_numeric,1|max_numeric,$lim|partysize,$lim",
        		'children' => "required|numeric|min_numeric,0|max_numeric,$lim|partysize,$lim",
        		'infants' => "required|numeric|min_numeric,0|max_numeric,$lim|partysize,$lim",
        	));
        	if ($data = $gump->run($request)) {
        		$br->setAdults($data['adults']);
        		$br->setChildren($data['children']);
        		$br->setInfants($data['infants']);
        		$br->save();
        		$this->redirect($this->Url('booking/ages'));
        	}
;
        }

        $this->View('header');
        $this->View('booking_numbers', array(
            'date' => $date,
            'time' => $time,
            'adultchoices' => $adultchoices,
            'childrenchoices' => $childrenchoices,
        	'infantchoices' => $infantchoices,
            'limit' => $limit,
        	'fares' => $fares,
        	'errors' => $gump->errors(),
        ));
        $this->View('footer');
    }


    public function agesAction() {
    	$bm = new bookingModel();
    	$br = new bookingRecord();
    	$gump = $this->getGump();

    	// need number of children
    	$children = $br->getChildren();
    	if (!$children) {
    		throw new \Exception('There are no children in this booking');
    	}

    	// form submitted?
    	if ($request = $this->getRequest()) {
    		if (!empty($request['cancel'])) {
    			$this->redirect($this->Url('booking/numbers'));
    		}

    		$rules = array();
    		for ($i=1; $i<=$children; $i++) {
    			$rules['sex'.$i] = "required|alpha";
    			$rules['age'.$i] = "required|numeric|min_numeric,1|max_numeric,15";
    		}
    		$gump->validation_rules($rules);
    		if ($data = $gump->run($request)) {
    			$ages = array();
    			$sexes = array();
    			for ($i=1; $i<=$children; $i++) {
    				$ages[$i] = $data['age'.$i];
    				$sexes[$i] = $data['sex'.$i];
    			}
                $br->setAges($ages);
                $br->setSexes($sexes);
    			$br->save();
    			$this->redirect($this->Url('booking/contact'));
    		}
    	}

    	$this->View('header');
    	$this->View('booking_ages', array(
    		'children' => $children,
    		'ages' => $bm->getAges(),
    	));
    	$this->View('footer');

    }

    public function contactAction() {
    	$bm = new bookingModel();
    	$br = new bookingRecord();
    	$gump = $this->getGump();

    	$this->View('header');
    	$this->View('booking_contact');
    	$this->View('footer');

    }
}
