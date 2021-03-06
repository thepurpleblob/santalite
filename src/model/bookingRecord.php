<?php

namespace thepurpleblob\santa\model;

class bookingRecord {
    
    protected $reference = null;
    
    protected $trainlimitid = null;

    protected $dateid = null;

    protected $timeid = null;

    protected $adults = null;

    protected $children = null;

    protected $infants = null;

    protected $ages = null;

    protected $sexes = null;

    protected $title = null;

    protected $firstname = null;

    protected $lastname = null;

    protected $email = null;

    protected $address1 = null;

    protected $address2 = null;

    protected $city = null;

    protected $postcode = null;
    
    protected $county = null;

    protected $country = null;

    protected $phone = null;
    
    protected $amount = null;

    protected $einfo = 0;
    
    public function setReference($reference) {
        $this->reference = $reference;
    }
    
    public function getReference() {
        return $this->reference;
    }
    
    public function setTrainlimitid($trainlimitid) {
        $this->trainlimitid = $trainlimitid;
    }
    
    public function getTrainlimitid() {
        return $this->trainlimitid;
    }

    public function setDateid($dateid) {
        $this->dateid = $dateid;
    }

    public function getDateid() {
        return $this->dateid;
    }

    public function setTimeid($timeid) {
        $this->timeid = $timeid;
    }

    public function getTimeid() {
        return $this->timeid;
    }

    public function setAdults($adults) {
        $this->adults = (int)$adults;
    }

    public function getAdults() {
        return $this->adults;
    }

    public function setChildren($children) {
        $this->children = (int)$children;
    }

    public function getChildren() {
        return $this->children;
    }

    public function setInfants($infants) {
        $this->infants = (int)$infants;
    }

    public function getInfants() {
        return $this->infants;
    }

    public function setAges($ages) {
        $this->ages = $ages;
    }

    public function getAges() {
        return $this->ages;
    }

    public function setSexes($sexes) {
        $this->sexes = $sexes;
    }

    public function getSexes() {
        return $this->sexes;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setFirstname($firstname) {
        $this->firstname = $firstname;
    }

    public function getFirstname() {
        return $this->firstname;
    }

    public function setLastname($lastname) {
        $this->lastname = $lastname;
    }

    public function getLastname() {
        return $this->lastname;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setAddress1($address1) {
        $this->address1 = $address1;
    }

    public function getAddress1() {
        return $this->address1;
    }

    public function setAddress2($address2) {
        $this->address2 = $address2;
    }

    public function getAddress2() {
        return $this->address2;
    }

    public function setCity($city) {
        $this->city = $city;
    }

    public function getCity() {
        return $this->city;
    }

    public function setPostcode($postcode) {
        $this->postcode = $postcode;
    }

    public function getPostcode() {
        return $this->postcode;
    }

    public function setCountry($country) {
        $this->country = $country;
    }

    public function getCountry() {
        return $this->country;
    }
    
    public function setCounty($county) {
        $this->county = $county;
    }
    
    public function getCounty() {
        return $this->county;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }

    public function getPhone() {
        return $this->phone;
    }
    
    public function setAmount($amount) {
        $this->amount = $amount;
    }
    
    public function getAmount() {
        return $this->amount;
    }

    public function setEinfo($einfo) {
        $this->eninfo = $einfo;
    }

    public function getEinfo() {
        return $this->einfo;
    }

    private function get($name, $default=null) {
        if (isset($_SESSION[$name])) {
            $this->$name = $_SESSION[$name];
        } else {
            $this->$name = $default;
        }
    }

    private function put($name) {
        if ($this->$name!==null) {
            $_SESSION[$name] = $this->$name;
        } else {
            unset($_SESSION[$name]);
        }
    }

    public function __construct() {
        
        $this->get('trainlimitid');
        $this->get('reference');
        $this->get('dateid');
        $this->get('timeid');
        $this->get('adults', 1);
        $this->get('children', 1);
        $this->get('infants', 0);
        $this->get('sexes', array());
        $this->get('ages', array());
        $this->get('title');
        $this->get('firstname');
        $this->get('lastname');
        $this->get('email');
        $this->get('address1');
        $this->get('address2');
        $this->get('city');
        $this->get('county');
        $this->get('postcode');
        $this->get('country');
        $this->get('phone');
        $this->get('einfo');
    }

    public function save() {
        
        // save update time
        $_SESSION['santa_updated'] = time();

        $this->put('trainlimitid');
        $this->put('reference');
        $this->put('dateid');
        $this->put('timeid');
        $this->put('adults');
        $this->put('children');
        $this->put('infants');
        $this->put('ages');
        $this->put('sexes');
        $this->put('title');
        $this->put('firstname');
        $this->put('lastname');
        $this->put('email');
        $this->put('address1');
        $this->put('address2');
        $this->put('city');
        $this->put('county');
        $this->put('postcode');
        $this->put('country');
        $this->put('phone');
        $this->put('einfo');
    }
    
    public function expired() {
        return !isset($_SESSION['santa_updated']);
    }
}
