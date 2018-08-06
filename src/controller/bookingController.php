<?php
/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Booking controller
 */

namespace thepurpleblob\santa\controller;

use thepurpleblob\core\coreController;
use thepurpleblob\santa\model\bookingModel;
use thepurpleblob\santa\model\bookingRecord;

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
                'br' => $br,
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

    		// get/set limit
    		$limit = $bm->getTrainlimit($dateid, $timeid);
    		$br->setTrainlimitid($limit->id());

    		$br->setTimeid($timeid);
    		$br->save();
    		$this->redirect($this->Url('booking/ages'));
    	}

        $this->View('header');
        $this->View('booking_time', array(
            'date' => $date,
            'times' => $times,
            'available' => $available,
            'seatsavailable' => $seatsavailable,
        	'seatsneeded' => $seatsneeded,
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
    		$this->redirect($this->Url('booking/contact'));
    	}

    	// form submitted?
    	if ($request = $this->getRequest()) {
    		if (!empty($request['cancel'])) {
    			$this->redirect($this->Url('booking/time'));
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
    		'chooseages' => $bm->getAges(),
    	    'ages' => $br->getAges(),
    	    'sexes' => $br->getSexes(),
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
        $br->setCounty($data['county']);
        $br->setCountry('GB');
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
    		    'address1' => 'required',
    		    //'address2' => '',
    		    'city' => 'required',
    		    'postcode' => 'required',
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
        
        // get the time and date

        // we need to come up with a booking code about now
        $purchaseid = $bm->updatePurchase($br);

        // get encrypted data to send to SagePay
        $crypt = $bm->crypt($br);

        $this->View('header');
        $this->View('booking_confirm', array(
                'br' => $br,
                'country' => $country,
                'fares' => $fares,
                'date' => $bm->getReadableDate($br->getDateid()),
                'time' => $bm->getReadableTime($br->getTimeid()),
                'price_adults' => $price_adults,
                'price_children' => $price_children,
                'price_total' => $price_total,
                'crypt' => $crypt,
        ));
        $this->View('footer');
    }

    private function confirmationEmail($purchase, $date, $time) {
        global $CFG;

        require_once($CFG->dirroot . '/lib/swiftmailer/swift_required.php' );

        // create mail transport
        $transport = \Swift_SmtpTransport::newInstance($CFG->smtp_host);

        // create mailer
        $mailer = \Swift_Mailer::newInstance($transport);

        // message text;
        $mb = "Dear {$purchase->firstname} {$purchase->surname}, \n\n";
        $mb .= "Thank you for booking the Santa Steam Special at the Bo'ness & Kinneil Railway,\n";
        $mb .= "West Lothian. Tickets will be sent approximately 4 weeks before your journey.\n";
        $mb .= "If there are any important details we need to know regarding your booking,\n";
        $mb .= "or if your ticket has not arrived within 7 days of travel, please contact us by email\n";
        $mb .= "at office@srps.org.uk or phone the Santa Line on {$CFG->help_number} (10.30am to 12 noon /\n";
        $mb .= "1pm to 2:30pm weekdays). Note that changes to your booking once your ticket has\n";
        $mb .= "been sent out may incur a £5 administration charge.\n\n";
        $mb .= "Please quote your booking reference, '{$purchase->bkgref}' in any correspondence.\n\n";
        $mb .= "We look forward to seeing you soon,\n";
        $mb .= "your Santa Steam Trains Team!\n\n\n";
        $mb .= "Your booking details...\n\n";
        $mb .= "Santa train booking : $date at $time\n";
        $mb .= "Adult ticket(s) purchased : {$purchase->adult}\n";
        $mb .= "Child ticket(s) purchased : {$purchase->child}\n";
        if ($purchase->infant) {
            $mb .= "Infants in party (no seats) : {$purchase->infant}\n";
        }
        $mb .= "Price paid : £" . number_format($purchase->payment/100, 2) . "\n";

        // create message
        $message = \Swift_Message::newInstance('Santa Steam Trains Confirmation - ' . $purchase->bkgref)
            ->setFrom(array('office@srps.org.uk' => 'SRPS Santa Trains'))
            ->setTo(array($purchase->email, $CFG->backup_email))
            ->setBody($mb);
        $result = $mailer->send($message);
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

        // Send confirmation email
        if ($purchase->status == 'OK') {
            $this->confirmationEmail(
                    $purchase,
                    $bm->getReadableDate($br->getDateid()),
                    $bm->getReadableTime($br->getTimeid())
            );
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
