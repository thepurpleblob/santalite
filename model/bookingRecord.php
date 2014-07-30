<?php

namespace model;

class bookingRecord {

	protected $dateid = null;

	protected $timeid = null;

	protected $adults = null;

	protected $children = null;

	protected $infants = null;
	
	protected $ages = null;
	
	protected $sexes = null;

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
		$this->adults = $adults;
	}

	public function getAdults() {
		return $this->adults;
	}

	public function setChildren($children) {
		$this->children = $children;
	}

	public function getChildren() {
		return $this->children;
	}

	public function setInfants($infants) {
		$this->infants = $infants;
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

	private function get($name) {
		if (isset($_SESSION[$name])) {
			$this->$name = $_SESSION[$name];
		} else {
			$this->$name = null;
		}
	}

	private function put($name) {
		if ($this->$name) {
			$_SESSION[$name] = $this->$name;
		} else {
			unset($_SESSION[$name]);
		}
	}

	public function __construct() {

		$this->get('dateid');
		$this->get('timeid');
		$this->get('adults');
		$this->get('children');
		$this->get('infants');
		$this->get('sexes');
		$this->get('ages');

	}

	public function save() {

		$this->put('dateid');
		$this->put('timeid');
		$this->put('adults');
		$this->put('children');
		$this->put('infants');
		$this->put('ages');
		$this->put('sexes');
	}
}