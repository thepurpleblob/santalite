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
    
    public function expiredAction() {
    	$this->View('header');
    	$this->View('booking_expired');
    	$this->View('footer');    	
    }

    public function startAction() {
        
        // invalidate the session (new booking)
        session_unset();
        
    	$br = new bookingRecord();
    	$br->save();
    	
        $this->View('header');
        $this->View('booking_start');
        $this->View('footer');
    }
    public function numbersAction() {
        global $CFG;
        
        $bm = new bookingModel();
        $br = new bookingRecord();
        $gump = $this->getGump();
         
        // check the session is still around
        if ($br->expired()) {
            $this->redirect($this->url('booking/expired'));
        }
            // get fares
        $fares = \ORM::for_table('fares')->find_one(1);

        // choices
        $adultchoices = $this->fill(1, $CFG->select_limit);
        $childrenchoices = $this->fill(0, $CFG->select_limit);
        $childrenchoices[0] = 'None';
        $infantchoices = $this->fill(0, $CFG->select_limit);
        $infantchoices[0] = 'None';

        // form submitted?
        if ($request = $this->getRequest()) {
            if (!empty($request['cancel'])) {
                $this->redirect($this->Url('booking/start'));
            }

            $lim = $CFG->select_limit;;
            $gump->validation_rules(array(
                    'adults' => "required|numeric|min_numeric,1|max_numeric,$lim",
                    'children' => "required|numeric|min_numeric,0|max_numeric,$lim",
                    'infants' => "required|numeric|min_numeric,0|max_numeric,$lim",
            ));
            if ($data = $gump->run($request)) {
                $br->setAdults($data['adults']);
                $br->setChildren($data['children']);
                $br->setInfants($data['infants']);
                $br->save();
                $this->redirect($this->Url('booking/date'));
            }
        }

        $this->View('header');
        $this->View('booking_numbers', array(
                'adultchoices' => $adultchoices,
                'childrenchoices' => $childrenchoices,
                'infantchoices' => $infantchoices,
                'fares' => $fares,
                'errors' => $gump->errors(),
        ));
        $this->View('footer');
    }
    
    public function dateAction($dateid=0) {
        $cal = $this->getLib('calendar');
        $bm = new bookingModel();
        $br = new bookingRecord();
        
        // check the session is still around
        if ($br->expired()) {
        	$this->redirect($this->url('booking/expired'));
        }
        
        // get the remaining seats counts
        list($pcounts, $dmax) = $bm->getRemaining();
        $seatsneeded = $br->getAdults() + $br->getChildren();

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
        $months = $bm->getMonthsDays($dates, $dmax, $seatsneeded);

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
    	
    	// check the session is still around
    	if ($br->expired()) {
    		$this->redirect($this->url('booking/expired'));
    	}
    	
    	// get the remaining seats counts
    	list($pcounts, $dmax) = $bm->getRemaining();
    	$seatsavailable = $pcounts[$br->getDateid()];
    	$seatsneeded = $br->getAdults() + $br->getChildren();

    	// need dateid from session
    	$dateid = $br->getDateid();
    	$date = \ORM::for_table('traindate')->find_one($dateid);
    	if (!$date) {
    		throw new \Exception('Traindate not found in database for id='.$dateid);
    	}

    	// get available times
    	$times = \ORM::for_table('traintime')->order_by_asc('time')->find_many();
    	
    	// check if any are actually available
    	$available = false;
    	foreach($times as $time) {
    	    if ($seatsavailable[$time->id()] > $seatsneeded) {
    	        $available = true;
    	    }
    	}

    	// check for submission
    	if ($timeid && $available) {
    		$time = \ORM::for_table('traintime')->find_one($timeid);
    		if (!$time) {
    			throw new \Exception('Time not found in database id='.$timeid);
    		}

    		$br->setTimeid($timeid);
    		$br->save();
    		$this->redirect($this->Url('booking/ages'));
    	}

        $this->View('header');
        $this->View('booking_time', array(
            'date' => $date,
            'times' => $times,
            'available' => $available,
        ));
        $this->View('footer');
    }


    public function agesAction() {
    	$bm = new bookingModel();
    	$br = new bookingRecord();
    	$gump = $this->getGump();
    	
    	// check the session is still around
    	if ($br->expired()) {
    		$this->redirect($this->url('booking/expired'));
    	}

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
    				$ages[$i] = (int)$data['age'.$i];
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
    
    private function set_record($br, $data) {
        $br->setTitle($data['title']);
        $br->setFirstname($data['firstname']);
        $br->setLastname($data['lastname']);
        $br->setEmail($data['email']);
        $br->setAddress1($data['address1']);
        $br->setAddress2($data['address2']);
        $br->setCity($data['city']);
        $br->setPostcode($data['postcode']);
        $br->setCountry($data['country']);
        $br->setPhone($data['phone']);
    }

    public function contactAction() {
    	$bm = new bookingModel();
    	$br = new bookingRecord();
    	$gump = $this->getGump();
    	$errors = array();
    	
    	// check the session is still around
    	if ($br->expired()) {
    		$this->redirect($this->url('booking/expired'));
    	}
    	
    	// list of countries (for select)
    	$countries = $bm->getCountries();
    	
    	// form submitted?
    	if ($request = $this->getRequest()) {
    		if (!empty($request['cancel'])) {
    			$this->redirect($this->Url('booking/ages'));
    		}
    		
    		$gump->validation_rules(array(
    			//'title' => '',
    			'firstname' => 'required|valid_name',
    		    'lastname' => 'required|valid_name',
    		    'email' => 'required|valid_email',
    		    'address1' => 'required|street_address',
    		    //'address2' => '',
    		    'city' => 'required',
    		    'postcode' => 'required',
    		    'country' => 'required',
    		    //'phone' => '',
    		));
    		$this->set_record($br, $request);
    		if ($data = $gump->run($request)) {
    		    $this->set_record($br, $data);
    		    $br->save();
    		    $this->redirect($this->Url('booking/confirm'));
    		}
    		$errors = $gump->get_readable_errors();
    	}

    	$this->View('header');
    	$this->View('booking_contact', array(
    		'br' => $br,
    		'countries' => $countries,
    	    'errors' => $errors,
    	));
    	$this->View('footer');
    }
    
    
    public function confirmAction() {
        $bm = new bookingModel();
        $br = new bookingRecord();
        
        // check the session is still around
        if ($br->expired()) {
            $this->redirect($this->url('booking/expired'));
        }
        
        // resave session just to bump its time
        $br->save();
        
        // list of countries (for select)
        $countries = $bm->getCountries();
        $country = $countries[$br->getCountry()];
        
        // get fares
        $fares = \ORM::for_table('fares')->find_one(1);
        
        // sums
        $price_adults = $br->getAdults() * $fares->adult / 100;
        $price_children = $br->getChildren() * $fares->child / 100;
        $price_total = $price_adults + $price_children;
        $br->setAmount($price_total);
        
        // we need to come up with a booking code about now
        $purchaseid = $bm->updatePurchase($br);
        
        // get encrypted data to send to SagePay
        $crypt = $bm->crypt($br);
        
        $this->View('header');
        $this->View('booking_confirm', array(
                'br' => $br,
                'country' => $country,
                'fares' => $fares,
                'price_adults' => $price_adults,
                'price_children' => $price_children,
                'price_total' => $price_total,
                'crypt' => $crypt,
        ));
        $this->View('footer');
    }
    
    public function returnAction($result) {
        $bm = new bookingModel();
        $br = new bookingRecord();
        
        // check the session is still around
        if ($br->expired()) {
            $this->redirect($this->url('booking/expired'));
        }
        
        if ($request = $_GET) {
            if (!isset($request['crypt'])) {
                throw new \Exception("No crypt field on return from SagePay");
            }
            $crypt = $request['crypt'];
            $purchase = $bm->decrypt($br, $crypt);
            
        }
        $this->View('header');
        $this->View('booking_result', array(
                'br' => $br,
                'purchase' => $purchase,
                'result' => $result,
        ));
        $this->View('footer');
    }
}
