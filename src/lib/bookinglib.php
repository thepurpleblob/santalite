<?php
/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Booking model
 */

namespace thepurpleblob\santa\lib;

class bookinglib {

    protected $dates;

    protected $times;

    public function __construct() {

        // Just about everything needs the dates and times of services
        $this->dates = \ORM::for_table('traindate')->order_by_asc('date')->find_many();
        $this->times = \ORM::for_table('traintime')->order_by_asc('time')->find_many();
    }

    /**
     * Get (cached) dates and times
     * @return array($dates, $times)
     */
    public function getDatesTimes() {
        return [$this->dates, $this->times];
    }

    /**
     * Get operating days
     * (Should be just Saturday and Sunday, but...)
     * @return array
     */
    public function getDays() {
        $days = [];
        foreach ($this->dates as $date) {
            $day = date('l', $date->date);
            $daynumber = date('N', $date->date);
            $days[$daynumber] = $day;
        }

        return array_values($days);
    }

    /**
     * Get availability message
     * @param $seatsremaining
     * @return string
     */
    protected function availableMessage($seatsremaining) {
        if ($seatsremaining < 50) {
            return 'Few seats left';
        } else {
            return 'Seats available';
        }
    }

    /**
     * Get times structure for getDateTimeSelect
     * @param int $dateid
     * @param array $seats (remaining)
     * @param int $seatsneeded
     * @return array
    */
    protected function getTimes($dateid, $seats = null, $seatsneeded = 0) {
        $times = [];
        foreach ($this->times as $time) {
            $slot = new \stdClass();
            $slot->time = $time->time;
            $slot->timeid = $time->id;
            $limit = \ORM::forTable('trainlimit')->where(['timeid' => $time->id, 'dateid' => $dateid])->findOne();
            $slot->trainlimitid = $limit->id;
            if ($seats) {
                $slot->remaining = $seats[$time->id];
                $slot->available = $slot->remaining > $seatsneeded;
                $slot->message = $this->availableMessage($slot->remaining);
            } else {
                $slot->remaining = 0;
                $slot->available = 0;
                $slot->message = '';
            }
            $times[] = $slot;
        }

        return $times;
    }

    /**
     * Get array of date/time selections for display
     * By...
     * 1. Week number
     *   2. Day number
     *      (Date)
     *     3. Time
     *        (Availability)
     * @param int $seatsneeded
     * @param array, array $pcounts
     * @return array
     */
     public function getDateTimeSelect($seatsneeded, $pcounts) {

        // Start with array of active days (e.g. Saturday/Sunday)
        $days = $this->getDays();

        $weeks = [];
        foreach ($this->dates as $date) {
            $day = new \stdClass;
            $weeknumber = date('W', $date->date); 
            if (empty($weeks[$weeknumber])) {
                $weeks[$weeknumber] = new \stdClass;
                $weeks[$weeknumber]->days = [];
            }
            $daynumber = date('N', $date->date);
            $day->weeknumber = $weeknumber;
            $day->daynumber = $daynumber;
            $day->id = $date->id;
            $day->dateid = $date->id;
            $day->date = date('l jS F', $date->date);
            $day->times = $this->getTimes($date->id, $pcounts[$date->id], $seatsneeded);
            $weeks[$weeknumber]->days[] = $day;
        }

        //echo "<pre>"; var_dump($weeks); die;
        return array_values($weeks);
     }    

    /**
     * get operating months and days therein(numbers)
     * encode month/year, bit of a bodge
     */
    public function getMonthsDays($dates, $dmax, $seatsneeded) {
        $months = array();
        foreach ($dates as $date) {
            $month = date('m/Y', $date->date);
            $day = date('j', $date->date);
            if (!isset($months[$month])) {
                $months[$month] = array();
            }
            if ($seatsneeded <= $dmax[$date->id()]) {
                $months[$month][$day] = $date->id;
            }
        }

        return $months;
    }
    
    /**
     * Get trainlimit
     */
    public function getTrainlimit($dateid, $timeid) {
        global $CFG;

        $limit = \ORM::for_table('trainlimit')->where(array(
                'dateid' => $dateid,
                'timeid' => $timeid,
        ))->find_one();
        if (!$limit) {
            
            // Possible for limit to be missing. Just create one
            $limit = \ORM::for_table('trainlimit')->create();
            $limit->dateid = $dateid;
            $limit->timeid = $timeid;
            $limit->maxlimit = $CFG->default_limit;
            $limit->partysize = $CFG->default_party;
            $limit->save();
        }
        return $limit;       
    }

    /**
     * Get (2d) array of passenger remaining counts
     * @return [array, array]
     */
    public function getRemaining() {
        $dates = \ORM::for_table('traindate')->order_by_asc('date')->find_many();
        $times = \ORM::for_table('traintime')->order_by_asc('time')->find_many();
        $pcounts = array();
        $daymax = array();
        foreach ($dates as $date) {
            $pcounts[$date->id()] = array();
            $daymax[$date->id()] = 0;
            foreach ($times as $time) {
                $limit = $this->getTrainlimit($date->id(), $time->id());
                $filter = array(
                        'trainlimitid' => $limit->id(),
                        'status' => 'OK',
                );
                $sumadult = \ORM::for_table('purchase')->where('trainlimitid', $limit->id())->where_like('status', 'OK%')->sum('adult');
                $sumchild = \ORM::for_table('purchase')->where('trainlimitid', $limit->id())->where_like('status', 'OK%')->sum('child');
                $total = $limit->maxlimit - ($sumadult + $sumchild);
                $pcounts[$date->id()][$time->id()] = $total;
                if ($total > $daymax[$date->id()]) {
                    $daymax[$date->id()] = $total;
                }
            }
        }
        return array($pcounts, $daymax);
    }

    /*
     * Create select array for child's ages
     * @param int $selected
     * @return array
     */
    public function getAges($selected = 0) {
        $age = new \stdClass;
        $age->key = 1;
        $age->value = '18-23 months';
        $age->selected = $selected == 1;
    	$ages = [$age];
    	for ($i=2; $i<=15; $i++) {
            $age = new \stdClass;
            $age->key = $i;
            $age->value = "$i years";
            $age->selected = $selected == $i;
            $ages[] = $age;
    	}
    	return $ages;
    }

    /**
     * Get the date in a readable format given dateid
     * @param unknown $dateid
     */
    public function getReadableDate($dateid) {
        $date = \ORM::for_table('traindate')->find_one($dateid);
        if (!$date) {
            throw new \Exception('Date not found in DB for id='.$dateid);
        }
        return date('jS F Y', $date->date);
    }

    /**
     * Get the time in a readable format given timeid
     *
     */
    public function getReadableTime($timeid) {
        $time = \ORM::for_table('traintime')->find_one($timeid);
        if (!$time) {
            throw new \Exception('Time not found in DB for id='.$timeid);
        }
        return $time->time;
    }

    /**
     * Make the SagePay basket
     * @param unknown $br
     */
    private function makeBasket($br) {
        return '';
    }

    /**
     * Filter for SagePay's crypt fields
     * @param unknown $value
     * @return unknown
     */
    private function filter($value, $maxchars, $filter) {

        // use sagepay's clean functions
        $value = \sagelib::cleanInput($value, $filter);

        // truncate to maxchars
        $value = substr($value, 0, $maxchars);

        return $value;
    }

    /**
     * Construct SagePay crypt string
     * @param unknown $br
     */
    public function crypt($br) {
        global $CFG;

        // sort some basic stuff
        $description = "Bo'ness & Kinneil Railway Santa Steam Train booking on " .
            $this->getReadableDate($br->getDateid()) . ' departing ' . $this->getReadableTime($br->getTimeid()) .
            ' reference ' . $CFG->sage_prefix . $br->getReference();

        // build transaction data into string
        $cryptfields = array(
            'VendorTXCode' => $CFG->sage_prefix . $br->getReference(),
            'Amount' => number_format($br->getAmount(), 2),
            'Currency' => 'GBP',
            'Description' => $description,
            'SuccessURL' => $CFG->www . '/index.php/booking/return/success',
            'FailureURL' => $CFG->www . '/index.php/booking/return/failure',
            'CustomerName' => $this->filter($br->getTitle() . ' ' . $br->getFirstname() . ' ' . $br->getLastname(), 100,
                CLEAN_INPUT_FILTER_ALPHABETIC_AND_ACCENTED),
            'CustomerEmail' => $this->filter($br->getEmail(), 100, CLEAN_INPUT_FILTER_WIDEST_ALLOWABLE_CHARACTER_RANGE),
            'VendorEmail' => $this->filter($CFG->sage_email, 255, CLEAN_INPUT_FILTER_WIDEST_ALLOWABLE_CHARACTER_RANGE),
            'SendEmail' => '1',
            'eMailMessage' => $this->filter($CFG->sage_message, 7500, CLEAN_INPUT_FILTER_WIDEST_ALLOWABLE_CHARACTER_RANGE),
            'BillingSurname' => $this->filter($br->getLastname(), 20, CLEAN_INPUT_FILTER_ALPHABETIC_AND_ACCENTED),
            'BillingFirstnames' => $this->filter($br->getFirstname(), 20, CLEAN_INPUT_FILTER_ALPHABETIC_AND_ACCENTED),
            'BillingAddress1' => $this->filter($br->getAddress1(), 100, CLEAN_INPUT_FILTER_ALPHANUMERIC_AND_ACCENTED),
            'BillingAddress2' => $this->filter($br->getAddress2(), 100, CLEAN_INPUT_FILTER_ALPHANUMERIC_AND_ACCENTED),
            'BillingCity' => $this->filter($br->getCity(), 40, CLEAN_INPUT_FILTER_ALPHANUMERIC_AND_ACCENTED),
            'BillingPostCode' => $this->filter($br->getPostcode(), 10, CLEAN_INPUT_FILTER_ALPHANUMERIC),
            'BillingCountry' => $br->getCountry(),
            'BillingPhone' => $this->filter($br->getPhone(), 20, CLEAN_INPUT_FILTER_ALPHANUMERIC),
            'DeliverySurname' => $this->filter($br->getLastname(), 20, CLEAN_INPUT_FILTER_ALPHABETIC_AND_ACCENTED),
            'DeliveryFirstnames' => $this->filter($br->getFirstname(), 20, CLEAN_INPUT_FILTER_ALPHABETIC_AND_ACCENTED),
            'DeliveryAddress1' => $this->filter($br->getAddress1(), 100, CLEAN_INPUT_FILTER_ALPHANUMERIC_AND_ACCENTED),
            'DeliveryAddress2' => $this->filter($br->getAddress2(), 100, CLEAN_INPUT_FILTER_ALPHANUMERIC_AND_ACCENTED),
            'DeliveryCity' => $this->filter($br->getCity(), 40, CLEAN_INPUT_FILTER_ALPHANUMERIC_AND_ACCENTED),
            'DeliveryPostCode' => $this->filter($br->getPostcode(), 10, CLEAN_INPUT_FILTER_ALPHANUMERIC),
            'DeliveryCountry' => $br->getCountry(),
            'DeliveryPhone' => $this->filter($br->getPhone(), 20, CLEAN_INPUT_FILTER_ALPHANUMERIC),
            'Basket' => $this->filter($this->makeBasket($br), 7500, CLEAN_INPUT_FILTER_WIDEST_ALLOWABLE_CHARACTER_RANGE),
        );

        // translate to name=value array
        $namevalue = array();
        foreach ($cryptfields as $name => $value) {
            $namevalue[] = "$name=$value";
        }

        $cryptstring = implode('&', $namevalue);

        return \sagelib::encryptAndEncode($cryptstring, $CFG->sage_encrypt);
    }

    /**
     * Get the nth date/time in the database for output record
     */
    private function getNth($table, $sort, $id) {
        $records = \ORM::for_table($table)->order_by_asc($sort)->find_many();
        $count = 1;
        foreach ($records as $record) {
            if ($record->id == $id) {
                return $count;
            }
            $count++;
        }
        throw new \Exception('ID '.$id.' not found in table '.$table);
    }

    /**
     * Convert boy/girl ages records into hex strings
     */
    private function encodeAges($br) {
        $ages = $br->getAges();
        $sexes = $br->getSexes();
        $boys = '';
        $girls = '';
        if ($sexes) {
            foreach ($sexes as $sex) {
                $age = array_shift($ages);
                if ($sex=='boy') {
                    $boys .= dechex($age);
                } else {
                    $girls .= dechex($age);
                }
            }
        }
        return array($boys, $girls);
    }

    /**
     * Calculate fares
     * @param object $br booking record
     * @return object
     */
    public function calculateFares($br) {

        $disp = new \stdClass;

        // get fares
        $fares = \ORM::for_table('fares')->find_one(1);
        $disp->fare_adult = number_format($fares->adult/100, 2);
        $disp->fare_child = number_format($fares->child/100, 2);

        // sums
        $price_adults = $br->getAdults() * $fares->adult / 100;
        $disp->price_adults = number_format($price_adults, 2);
        $price_children = $br->getChildren() * $fares->child / 100;
        $disp->price_children = number_format($price_children, 2);
        $price_total = $price_adults + $price_children;
        $disp->price_total = number_format($price_total, 2);
        $br->setAmount($price_total);

        return $disp;
    }

    /**
     * Get purchase record
     * @param object $br
     */
    public function getPurchase($br) {
        $purchase = \ORM::for_table('purchase')->find_one($br->getReference());
        if (!$purchase) {
            throw new \Exception('Cannot find record in DB for purchase id='.$br->getReference());
        }

        return $purchase;
    }

    /**
     * Find the purchase from the VendorTxCode
     * (Same as our bkgref)
     * @param string $VendorTxCode
     * @return mixed Purchase record of false if not found
     */
    public function getPurchaseFromVendorTxCode($VendorTxCode) {
        $purchase = \ORM::forTable('purchase')->where('bkgref', $VendorTxCode)->findOne();

        return $purchase;
    }

    /**
     * Update or create purchase record (checks for id)
     * @param unknown $br
     */
    public function updatePurchase($br) {
        global $CFG;

        // if br has a reference number this should match the database record
        if ($br->getReference()) {
            $purchase = \ORM::for_table('purchase')->find_one($br->getReference());
            if (!$purchase) {
                throw new \Exception('Cannot find record in DB for purchase id='.$br->getReference());
            }
        } else {
            $purchase = \ORM::for_table('purchase')->create();
        }

        // update all the data (can't update reference until sure we have an ID)
        $purchase->type = 'O';
        $purchase->trainlimitid = $br->getTrainlimitid();
        $purchase->day = $this->getNth('traindate', 'date', $br->getDateid());
        $purchase->train = $this->getNth('traintime', 'time', $br->getTimeid());
        $purchase->surname = $br->getLastname();
        $purchase->title = $br->getTitle();
        $purchase->firstname = $br->getFirstname();
        $purchase->address1 = $br->getAddress1();
        $purchase->address2 = $br->getAddress2();
        $purchase->address3 = $br->getCity();
        $purchase->address4 = $br->getCounty();
        $purchase->postcode = $br->getPostcode();
        $purchase->phone = $br->getPhone();
        $purchase->email = $br->getEmail();
        $purchase->adult = $br->getAdults();
        $purchase->child = $br->getChildren();
        $purchase->infant = $br->getInfants();
        $purchase->oap = 0;
        list($purchase->childagesboy, $purchase->childagesgirl) = $this->encodeAges($br);
        $purchase->comment = '';
        $purchase->payment = floor($br->getAmount() * 100);
        $purchase->bkgdate = date('Ymd');
        $purchase->card = 'Y';
        $purchase->action = 'N';
        $purchase->eticket = 'N';
        $purchase->einfo = $br->getEinfo() ? 'Y' : 'N';
        $purchase->bankauthcode = '-';
        $purchase->declinecode = '-';
        $purchase->emailsent = 0;
        $purchase->completed = 0;

        $purchase->save();
        $br->setReference($purchase->id());
        $purchase->bkgref = $CFG->sage_prefix . $br->getReference();
        $purchase->save();
        $br->save();

        return $br->getReference();
    }

    /**
     * Update purchase with data returned from SagePay
     * @param object $purchase
     * @param array $data
     * @return purchase
     */
    public function updateSagepayPurchase($purchase, $data) {
        $purchase->status = $data['Status'];

        $purchase->statusdetail = $data['StatusDetail'];
        $purchase->cardtype = $data['CardType'];
        $purchase->last4digits = empty($data['Last4Digits']) ? '0000' : $data['Last4Digits'];
        $purchase->bankauthcode = empty($data['BankAuthCode']) ? '0000' : $data['BankAuthCode'];
        $purchase->declinecode = empty($data['DeclineCode']) ? '0000' : $data['DeclineCode'];
        $purchase->completed = 1;
        $purchase->save();

        return $purchase;
    }

    /**
     * Decrypt returndata from sage
     */
    public function decrypt($br, $crypt) {
        global $CFG;

        $decode = \sagelib::decodeAndDecrypt($crypt, $CFG->sage_encrypt);
        $pairs = explode('&', $decode);
        $data = array();
        foreach($pairs as $pair) {
            $split = explode('=', $pair);
            $data[$split[0]] = $split[1];
        }

        // Update the database record
        $purchase = \ORM::for_table('purchase')->find_one($br->getReference());
        if (!$purchase) {
            throw new \Exception('Cannot find record in DB for purchase id='.$br->getReference());
        }
        
        // if the purchase already has a status then something is wrong
        if (($purchase->status == 'OK') || ($purchase->status == 'OK REPEATED')) {
            throw new \Exception('This sale has already been successfully recorded.');
        }

        $purchase->bkgref = $data['VendorTxCode'];
        $purchase->status = $data['Status'];
        $purchase->statusdetail = $data['StatusDetail'];
        $purchase->txauthno = isset($data['TxAuthNo']) ? $data['TxAuthNo'] : '-';
        $purchase->last4digits = isset($data['Last4Digits']) ? $data['Last4Digits'] : '-';
        $purchase->save();

        return $purchase;
    }

    /**
     * Get some stats for the admin/index page
     * @return object
     */
    public function getStats() {
        $stats = new \stdClass;
        $stats->all_count = \ORM::for_table('purchase')->where_like('status', 'OK%')->count();
        $stats->all_sum = number_format(\ORM::for_table('purchase')->where_like('status', 'OK%')->sum('payment') / 100, 2);
        
        $today = date('Ymd');
        $stats->today_count = \ORM::for_table('purchase')->where_like('status', 'OK%')->where('bkgdate', $today)->count();
        $stats->today_sum = number_format(\ORM::for_table('purchase')->where_like('status', 'OK%')->where('bkgdate', $today)->sum('payment') / 100, 2);

        return $stats;
    }

}

