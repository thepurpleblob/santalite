<?php
/**
 * Created by PhpStorm.
 * User: howard
 * Date: 27/03/2016
 * Time: 22:14
 */

namespace thepurpleblob\santa\lib;

use Exception;
use FluidXml\FluidXml;

class sagepayserverlib {

    protected $purchase;

    protected $service;

    protected $basket;

    protected $fare;

    protected $error;

    /**
     * Booking constructor.
     * @param $controller
     */
    public function __construct() {
        $this->error = '';
    }

    public function setPurchase($purchase) {
        $this->purchase = $purchase;
    }

    public function setService($service) {
        $this->service = $service;
    }

    public function setFare($fare) {
        $this->fare = $fare;
    }

    /**
     * The data sent to sage, some basic checks
     * @param string $data
     */
    private function clean($data, $maxlength=255) {

        // Ampersand is used as a separator for records
        $data = str_replace('&', 'and', $data);

        // Equals is used as a name=value separator
        $data = str_replace('=', 'equals', $data);

        // trim
        $data = trim($data);

        // clip to length
        $data = substr($data, 0, $maxlength);

        return $data;
    }

    /**
     * Build the XML basket
     */
    private function buildBasket() {
        $basket = new FluidXml('basket', ['encoding' => 'UTF-8']);

        // booked class
        $class = $this->purchase->class=='F' ? 'First' : 'Standard';

        // Adult purchases
        $aname = $this->purchase->adults==1 ? 'Adult' : 'Adults';
        $basket->add('item', true)
            ->add('description', htmlentities("Railtour '" . $this->service->name . "' $aname in $class Class"))
            ->add('quantity', $this->purchase->adults)
            ->add('unitNetAmount', number_format($this->fare->adultunit, 2))
            ->add('unitTaxAmount', '0.00')
            ->add('unitGrossAmount', number_format($this->fare->adultunit, 2))
            ->add('totalGrossAmount', number_format($this->fare->adultfare, 2));

        // Child purchases
        if ($this->purchase->children) {
            $aname = $this->purchase->children==1 ? 'Child' : 'Children';
            $basket->add('item', true)
                ->add('description', htmlentities("Railtour '" . $this->service->name . "' $aname in $class Class"))
                ->add('quantity', $this->purchase->children)
                ->add('unitNetAmount', number_format($this->fare->childunit, 2))
                ->add('unitTaxAmount', '0.00')
                ->add('unitGrossAmount', number_format($this->fare->childunit, 2))
                ->add('totalGrossAmount', number_format($this->fare->childfare, 2));
        }

        // Meals
        foreach (['a', 'b', 'c', 'd'] as $c) {
            $name = 'meal' . $c;
            if ($this->service->{$name . 'visible'} && $this->purchase->$name) {
                $total = $this->purchase->$name * $this->service->{$name . 'price'};
                $basket->add('item', true)
                    ->add('description', htmlentities($this->service->{$name . 'name'}))
                    ->add('quantity', $this->purchase->$name)
                    ->add('unitNetAmount', $this->service->{$name . 'price'})
                    ->add('unitTaxAmount', '0.00')
                    ->add('unitGrossAmount', $this->service->{$name . 'price'})
                    ->add('totalGrossAmount', number_format($total, 2));
            }
        }

        // Seat supplement
        if ($this->fare->seatsupplement) {
            $passengers = $this->purchase->adults + $this->purchase->children;
            $basket->add('item', true)
                ->add('description', 'Window seat supplement')
                ->add('quantity', $passengers)
                ->add('unitNetAmount', $this->service->singlesupplement)
                ->add('unitTaxAmount', 0)
                ->add('unitGrossAmount', $this->service->singlesupplement)
                ->add('totalGrossAmount', number_format($this->fare->seatsupplement, 2));
        }

        $dom = $basket->dom();
        $dom->formatOutput = false;
        $xml = $dom->saveXML($dom->documentElement);

        return trim($xml);
    }

    /**
     * Build associative array of registration data
     */
    private function buildRegistrationData() {
        global $CFG;

        $data = [
            'VPSProtocol' => '3.00',
            'TxType' => 'PAYMENT',
            'Vendor' => $CFG->sage_vendor,
            'VendorTxCode' => $this->purchase->bookingref,
            'Amount' => number_format($this->purchase->payment,2),
            'Currency' => 'GBP',
            'Description' => $this->clean("SRPS Railtour Booking - " . $this->service->name, 100),
            'NotificationURL' => $this->controller->Url('booking/notification'),
            'BillingSurname' => $this->clean($this->purchase->surname, 20),
            'BillingFirstnames' => $this->clean($this->purchase->firstname, 20),
            'BillingAddress1' => $this->clean($this->purchase->address1, 100),
            'BillingAddress2' => $this->clean($this->purchase->address2, 100),
            'BillingCity' => $this->clean($this->purchase->city, 40),
            'BillingPostCode' => $this->clean($this->purchase->postcode, 10),
            'BillingCountry' => 'GB', // TODO (maybe)
            'DeliverySurname' => $this->clean($this->purchase->surname, 20),
            'DeliveryFirstnames' => $this->clean($this->purchase->firstname, 20),
            'DeliveryAddress1' => $this->clean($this->purchase->address1, 100),
            'DeliveryAddress2' => $this->clean($this->purchase->address2, 100),
            'DeliveryCity' => $this->clean($this->purchase->city, 40),
            'DeliveryPostCode' => $this->clean($this->purchase->postcode, 10),
            'DeliveryCountry' => 'GB', // TODO (maybe)
            'CustomerEmail' => $this->clean($this->purchase->email, 255),
            'BasketXML' => $this->clean($this->buildBasket(), 20000),
            'AllowGiftAid' => 1,
            'AccountType' => $this->purchase->bookedby ? 'M' : 'E', 
        ];

        // turn these into name=value
        $params = [];
        foreach ($data as $name => $value) {
            $params[] = "$name=$value";
        }
        $post = implode('&', $params);

        return $post;
    }

    /**
     * Register Purchase with Sagepay
     */
    public function register() {
        global $CFG;

        // get the POST data
        $data = $this->buildRegistrationData();

        // send it off to SagePay
        $curl = curl_init($CFG->sage_url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);

        // if result is (exactly) false something is amiss
        if ($result === false) {
            $this->error = curl_error($curl);
            return false;
        }

        // result consists of name=value on new lines
        $lines = explode(PHP_EOL, $result);
        $sr = [];
        foreach ($lines as $line) {
            $parts = explode('=', $line, 2);
            $sr[$parts[0]] = trim($parts[1]);
        }

        return $sr;
    }

    /**
     * Get fields from POST data.
     * @param array list of field names
     * @return array assoc array of results (or blanks)
     */
    private function getFields($names) {
        $values = array();
        $data = $this->controller->getRequest();
        foreach ($names as $name) {
            if (isset($_POST[$name])) {
                $values[$name] = $_POST[$name];
            } else {
                $values[$name] = '';
            }
        }

        return $values;
    }

    /**
     * Get the SagePay response data
     * @return assoc array of the data
     */
    public function getNotification() {
        $fields = [
            'VPSProtocol',
            'TxType',
            'VendorTxCode',
            'VPSTXId',
            'Status',
            'StatusDetail',
            'TxAuthNo',
            'AVSCV2',
            'AddressResult',
            'PostCodeResult',
            'CV2Result',
            'GiftAid',
            '3DSecureStatus',
            'CAVV',
            'AddressStatus',
            'PayerStatus',
            'CardType',
            'Last4Digits',
            'VPSSignature',
            'FraudResponse',
            'Surcharge',
            'DeclineCode',
            'ExpiryDate',
            'BankAuthCode',
            'Token'
        ];

        return $this->getFields($fields);
    }

    /**
     * Check VPSSignature
     * Complicated calculation
     * @param object $purchase
     * @param array $nvals notification data from SagePay
     * @return boolean true if matches
     *
     */
    public function checkVPSSignature($purchase, $nvals) {
        global $CFG;

        // concatenate various fields
        $values = [
            $purchase->VPSTxId,
            $nvals['VendorTxCode'],
            $nvals['Status'],
            isset($nvals['TxAuthNo']) ? $nvals['TxAuthNo'] : '',
            $CFG->sage_vendor,
            $nvals['AVSCV2'],
            $purchase->securitykey,
            $nvals['AddressResult'],
            $nvals['PostCodeResult'],
            $nvals['CV2Result'],
            $nvals['GiftAid'],
            $nvals['3DSecureStatus'],
            isset($nvals['CAVV']) ? $nvals['CAVV'] : '',
            isset($nvals['AddressStatus']) ? $nvals['AddressStatus'] : '',
            isset($nvals['PayerStatus']) ? $nvals['PayerStatus'] : '',
            $nvals['CardType'],
            $nvals['Last4Digits'],
            isset($nvals['DeclineCode']) ? $nvals['DeclineCode'] : '',
            $nvals['ExpiryDate'],
            isset($nvals['FraudResponse']) ? $nvals['FraudResponse'] : '',
            isset($nvals['BankAuthCode']) ? $nvals['BankAuthCode'] : '',
        ];

        // Calculate our version of signature
        $oursignature = strtoupper(md5(implode($values)));

        $match = $oursignature == $nvals['VPSSignature'];

        // If it fails log the trouble.
        if (!$match) {
	    $this->controller->log('VPSSignature mismatch - ' . var_export($values, true));
            $this->controller->log('Notification response - ' . var_export($nvals, true));
            $this->controller->log('Purchase record - ' . var_export($purchase, true));
            $this->controller->log('Our MD5 is ' . $oursignature);
            $this->controller->log('Their MD5 is ' . $nvals['VPSSignature']);
        }

        return $match;
    }

    /**
     * Notification receipt
     *
     */
    public function notificationreceipt($Status, $RedirectURL, $StatusDetail) {
        echo "Status=$Status\n";
        echo "RedirectURL=$RedirectURL\n";
        echo "StatusDetail=$StatusDetail\n";
        die;
    }

}
