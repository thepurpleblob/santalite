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
use thepurpleblob\santa\lib\sagepayserverlib;
use thepurpleblob\santa\lib\maillib;

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
                $cancel = !empty($request['cancel']);

    		$rules = array();
                $required = $cancel ? '' : 'required|';
    		for ($i=1; $i<=$children; $i++) {
    			$rules['sex'.$i] = "{$required}alpha";
    			$rules['age'.$i] = "{$required}numeric|min_numeric,1|max_numeric,15";
                \GUMP::set_field_name('sex'.$i, "Girl/Boy number $i");
                \GUMP::set_field_name('age'.$i, "Age number $i");
    		}
    		$gump->validation_rules($rules);
    		if ($data = $gump->run($request)) {
    			$ages = array();
    			$sexes = array();
    			for ($i=1; $i<=$children; $i++) {
    				$ages[$i] = isset($data['age'.$i]) ? (int)$data['age'.$i] : 0;
    				$sexes[$i] = isset($data['sex'.$i]) ? $data['sex'.$i] : '';
    			} 
                        $br->setAges($ages);
                        $br->setSexes($sexes);
    			$br->save();
                        if ($cancel) {
    			    $this->redirect($this->Url('booking/date'));
                        } else {
    			    $this->redirect($this->Url('booking/contact'));
                        }
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

    /**
     * Set contact data in booking record
     * @param object $br
     * @param array $data
     */
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

    /**
     * Get contact information
     */
    public function contactAction() {
    	$br = new bookingRecord();
    	$gump = $this->getGump();
    	$errors = array();

    	// check the session is still around
    	if ($br->expired()) {
    		$this->redirect($this->url('booking/expired'));
    	}

    	// form submitted?
    	if ($request = $this->getRequest()) {
                $cancel = !empty($request['cancel']);

                $required = $cancel ? '' : 'required|';
    		$rules = array(
                    'firstname' => $required . 'valid_name',
                    'lastname' => $required . 'valid_name',
                    'email' => $required . 'valid_email',
    		);
                if ($required) {
    		    $rules['address1'] = 'required';
                    $rules['city'] = 'required';
                    $rules['postcode'] = 'required';
                }
    		$gump->validation_rules($rules);

    		$this->set_record($br, $request);
    		if ($data = $gump->run($request)) {
    		    $this->set_record($br, $data);
    		    $br->save();
                    if ($cancel) {
    			$this->redirect($this->Url('booking/ages'));
                    } else {
    		        $this->redirect($this->Url('booking/confirm'));
                    }
    		}
    		$errors = $gump->get_readable_errors();
    	}

        // Create form
        $form = new \stdClass;
        $form->title = $this->form->text('title', 'Title', $br->getTitle());
        $form->firstname = $this->form->text('firstname', 'First name(s)', $br->getFirstname(), true);
        $form->lastname = $this->form->text('lastname', 'Last name', $br->getLastname(), true);
        $form->email = $this->form->text('email', 'Email', $br->getEmail(), true, null, 'email');
        $form->address1 = $this->form->text('address1', 'Address line 1', $br->getAddress1(), true);
        $form->address2 = $this->form->text('address2', 'Address line 2', $br->getAddress2());
        $form->city = $this->form->text('city', 'Town/city', $br->getCity(), true);
        $form->county = $this->form->text('county', 'County', $br->getCounty());
        $form->postcode = $this->form->text('postcode', 'Postcode', $br->getPostcode(), true);
        $form->phone = $this->form->text('phone', 'Phone', $br->getPhone());
        $form->buttons = $this->form->buttons('Next', 'Back', true);

    	$this->View('booking_contact', array(
    		'br' => $br,
            'form' => $form,
    	    'errors' => $errors,
    	));
    }


    public function confirmAction() {
        $br = new bookingRecord();

        // check the session is still around
        if ($br->expired()) {
            $this->redirect($this->url('booking/expired'));
        }

        // resave session just to bump its time
        $br->save();

        // get fares
        $fares = $this->bm->calculateFares($br);

        // date/time
        $date = $this->bm->getReadableDate($br->getDateid());
        $time = $this->bm->getReadableTime($br->getTimeid());
        
        // we need to come up with a booking code about now
        $purchaseid = $this->bm->updatePurchase($br);

        $this->View('booking_confirm', array(
                'br' => $br,
                'fare_adult' => $fares->fare_adult,
                'fare_child' => $fares->fare_child,
                'date' => $date,
                'time' => $time,
                'price_adults' => $fares->price_adults,
                'price_children' => $fares->price_children,
                'price_total' => $fares->price_total,
        ));
    }

    /**
     * This is a bit different - we get here from the
     * review page, only if the form is submitted.
     * This action sends the payment registration to SagePay
     */
    public function paymentAction() {

        $br = new bookingRecord();

        // check the session is still around
        if ($br->expired()) {
            $this->redirect($this->url('booking/expired'));
        }

        // resave session just to bump its time
        $br->save();

        // Get the purchase record
        $purchase = $this->bm->getPurchase($br);

        // work out final fare
        $fares = \ORM::forTable('fares')->findOne(1);

        // Line up Sagepay class
        $sagepay = new sagepayserverlib();
        $sagepay->setController($this);
        $sagepay->setPurchase($purchase);
        $sagepay->setFare($this->bm->calculateFares($br));

        // anything submitted?
        if ($data = $this->getRequest()) {

            // Anything other than 'next' jumps back
            if (empty($data['next'])) {
                $this->redirect($this->Url('booking/contact'));
            }

            // If we get here we can process SagePay stuff
            // Register payment with Sagepay
            $sr = $sagepay->register();

            // If false is returned then it went wrong
            if ($sr === false) {
                $this->View('booking_fail', array(
                    'status' => 'N/A',
                    'diagnostic' => $sagepay->getError(),
                ));
            }

            // check status of registration from SagePay
            $status = $sr['Status'];
            if (($status != 'OK') && ($status != 'OK REPEATED')) {
                $this->View('booking_fail', array(
                    'status' => $status,
                    'diagnostic' => $sr['StatusDetail'],
                ));
            }

            // update purchase
            $purchase->securitykey = $sr['SecurityKey'];
            $purchase->regstatus = $status;
            $purchase->VPSTxId = $sr['VPSTxId'];
            $purchase->save();

            // redirect to Sage
            $url = $sr['NextURL'];
            header("Location: $url");
            die;
        }
    }

    /**
     * Sagepay sends a POST notification when the payment is complete
     * NB: We *cannot* assume anything about our payment timeout anymore
     * Bear in mind that we can't interact with the user either (server-2-server)
     * @return mixed
     * @throws \Exception
     */
    public function notificationAction() {
        global $CFG;

        // Full strength error logging
        error_reporting(E_ALL);
        ini_set('display_errors', 0);
        ini_set('log_errors', 1);

        // Library stuff
        $sagepay = new sagepayserverlib();
        $sagepay->setController($this);

        // POST data from SagePay
        $data = $sagepay->getNotification();

        // Log the notification data to debug file (in case it's interesting)
        $this->log(var_export($data, true));

        // Get the VendorTxCode and use it to look up the purchase
        $VendorTxCode = $data['VendorTxCode'];
        if (!$purchase = $this->bm->getPurchaseFromVendorTxCode($VendorTxCode)) {
            $url = $this->Url('booking/fail') . '/' . $VendorTxCode . '/' . urlencode('Purchase record not found');
            $this->log('SagePay notification: Purchase not found - ' . $url);
            $sagepay->notificationreceipt('INVALID', $url, 'Purchase record not found');
            die;
        }

        // Now that we have the purchase object, we can save whatever we got back in it
        $purchase = $this->bm->updateSagepayPurchase($purchase, $data);

        // Mailer
        $mail = new maillib();
        $mailpurchase = clone $purchase;
        $mail->initialise($this, $mailpurchase, $this->bm);
        $mail->setExtrarecipients($CFG->backup_email);

        // Check VPSSignature for validity
        if (!$sagepay->checkVPSSignature($purchase, $data)) {
            $purchase->status = 'VPSFAIL';
            $purchase->save();
            $url = $this->Url('booking/fail') . '/' . $VendorTxCode . '/' . urlencode('VPSSignature not matched');
            $this->log('SagePay notification: VPS sig no match - ' . $url);
            $sagepay->notificationreceipt('INVALID', $url, 'VPSSignature not matched');
            die;
        }

        // Check Status.
        // Work out what next action should be
        $status = $purchase->status;
        if ($status == 'OK') {

            // Send confirmation email
            $url = $this->Url('booking_complete') . '/' . $VendorTxCode;
            $mail->confirm();
            $this->log('SagePay notification: Confirm sent - ' . $url);
            $sagepay->notificationreceipt('OK', $url, '');
        } else if ($status == 'ERROR') {
            $url = $this->Url('booking/fail') . '/' . $VendorTxCode . '/' . urlencode($purchase->statusdetail);
            $this->log('SagePay notification: Booking fail - ' . $url);
            $mail->decline();
            $sagepay->notificationreceipt('OK', $url, $purchase->statusdetail);
        } else {
            $url = $this->Url('booking/decline') . '/' . $VendorTxCode;
            $this->log('SagePay notification: Booking decline - ' . $url);
            $mail->decline();
            $sagepay->notificationreceipt('OK', $url, $purchase->statusdetail);
        }

        die;
    }

    /**
     * Fail action - we get here if we return error to SagePay
     * SagePay then redirects here
     * @param string $VendorTxCode
     * @param string $message
     */
    public function failAction($VendorTxCode, $message) {
        $message = urldecode($message);
        if (!$purchase = $this->bm->getPurchaseFromVendorTxCode($VendorTxCode)) {
            $this->View('booking_fail', array(
                'status' => 'N/A',
                'diagnostic' => 'Purchase record could not be found for ' . $VendorTxCode . ' Plus ' . $message,
            ));
        } else {
            $this->View('booking/fail', array(
                'status' => 'N/A',
                'diagnostic' => $message,
            ));
        }
    }

    /**
     * Complete action - SagePay returns here when all successful
     * SagePay then redirects here
     * @param string $VendorTxCode
     */
    public function completeAction($VendorTxCode) {
        if (!$purchase = $this->bm->getPurchaseFromVendorTxCode($VendorTxCode)) {
            $this->View('booking_fail', array(
                'status' => 'N/A',
                'diagnostic' => 'Purchase record could not be found for ' . $VendorTxCode,
            ));
        } else {
            $path = $purchase->bookedby ? 'booking_telephonecomplete' : 'booking_complete';
            $this->View($path, array(
                'purchase' => $purchase,
            ));
        }
    }

    /**
     * Decline action - SagePay returns here when payment declined
     * SagePay then redirects here
     * @param string $VendorTxCode
     */
    public function declineAction($VendorTxCode) {
        if (!$purchase = $this->bm->getPurchaseFromVendorTxCode($VendorTxCode)) {
            $this->View('booking_fail', array(
                'status' => 'N/A',
                'diagnostic' => 'Purchase record could not be found for ' . $VendorTxCode,
            ));
        } else {
            $this->View('booking_decline', array(
                'purchase' => $purchase,
            ));
        }
    }

}
