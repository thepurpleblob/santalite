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
use thepurpleblob\santa\lib\bookinglib;
use thepurpleblob\santa\model\bookingRecord;
use thepurpleblob\santa\lib\calendarlib;

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
        $this->bm = new bookinglib;
    }

    public function expiredAction() {
    	$this->View('booking_expired');
    }

    public function startAction() {

        // invalidate the session (new booking)
        session_unset();

    	$br = new bookingRecord();
    	$br->save();

        $this->View('booking_start');
    }
    
    /**
     * Display number travelling page
     */
    public function numbersAction() {
        global $CFG;

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
     
        // Form
        $form = new \stdClass;
        $form->adults = $this->form->select('adults',
            'Number of adults - £'.number_format($fares->adult/100, 2).' each',
            $br->getAdults(),
            $adultchoices,
            '',
            8);
        $form->children = $this->form->select('children',
            'Number of children - £'.number_format($fares->child/100, 2).' each <small class="santa-subtext">(2 years to 15 years)</small>',
            $br->getChildren(),
            $childrenchoices,
            '',
            8);
        $form->infants = $this->form->select('infants',
            'Number of infants <small class="santa-subtext">(younger than 2 years on day of travel)</small>',
            $br->getInfants(),
            $infantchoices,
            '',
            8);
        $form->buttons = $this->form->buttons('Next', 'Back', true);

        $this->View('booking_numbers', array(
                'br' => $br,
                'form' => $form,
                'adultchoices' => $adultchoices,
                'childrenchoices' => $childrenchoices,
                'infantchoices' => $infantchoices,
                'fares' => $fares,
                'errors' => $gump->errors(),
        ));
    }

    /**
     * Display date picker page
     */
    public function dateAction() {
        $cal = new calendarlib;
        $br = new bookingRecord();
        $gump = $this->getGump();

        // check the session is still around
        if ($br->expired()) {
        	$this->redirect($this->url('booking/expired'));
        }

        // get the needed seats and remaining counts
        $seatsneeded = $br->getAdults() + $br->getChildren();
        list($pcounts, $daymax) = $this->bm->getRemaining();

        // process data
        $errors = [];
        if ($request = $this->getRequest()) {

            // Validate
            $gump->validation_rules(array(
                    'dateid' => "required|numeric|min_numeric,1",
                    'timeid' => "required|numeric|min_numeric,1",
            ));

            if ($data = $gump->run($request)) {
                $dateid = $data['dateid'];
                $timeid = $data['timeid'];

                // Recheck limit
                if (!isset($pcounts[$dateid][$timeid])) {
                    throw new \Exception('Invalid date or time somehow selected!');
                }
                if ($pcounts[$dateid][$timeid] < $seatsneeded) {
                    $errors[] = 'Unfortunately, there are no longer seats available on your selected service';
                } else {
                    $br->setDateid($dateid);
                    $br->setTimeid($timeid);
                    $br->save();
                    $this->redirect($this->Url('booking/ages'));
                }
            }
        }

        // Operating days
        $days = $this->bm->getDays();

        // Build select
        $structure = $this->bm->getDateTimeSelect($seatsneeded, $pcounts);

        // build calendars
        $this->View('booking_date', array(
            'days' => $days,
            'structure' => $structure,
            'errors' => $errors,
        ));
    }

    /**
     * Get children's ages
     */
    public function agesAction() {
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
        $errors = [];
    	if ($request = $this->getRequest()) {
    		if (!empty($request['cancel'])) {
    			$this->redirect($this->Url('booking/date'));
    		}

    		$rules = array();
    		for ($i=1; $i<=$children; $i++) {
    			$rules['sex'.$i] = "required|alpha";
    			$rules['age'.$i] = "required|numeric|min_numeric,1|max_numeric,15";
                \GUMP::set_field_name('sex'.$i, "Girl/Boy number $i");
                \GUMP::set_field_name('age'.$i, "Age number $i");
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
            $errors = $gump->get_readable_errors();
    	}

        // Options
        $ages = $br->getAges();
        $sexes = $br->getSexes();

        // Create form (array of child data)
        $forms = [];
        for ($i = 1; $i <= $children; $i++) {
            $child = new \stdClass;
            $child->number = $i;
            $child->boychecked = isset($sexes[$i]) && ($sexes[$i] == 'boy');
            $child->girlchecked = isset($sexes[$i]) && ($sexes[$i] == 'girl');
            $selected = empty($ages[$i]) ? 0 : $ages[$i];
            $child->chooseages = $this->bm->getAges($selected);
            $forms[] = $child;
        }
        $buttons = $this->form->buttons('Next', 'Back', true);


    	$this->View('booking_ages', array(
    		'forms' => $forms,
            'errors' => $errors,
            'buttons' => $buttons,
    	));
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
