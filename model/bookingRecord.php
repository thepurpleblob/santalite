<?php

namespace model;

class bookingRecord {
	
	protected $dateid = null;
	
	protected $timeid = null;
	
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
	
	public function __construct() {
		$sess = $_SESSION;
		
		if (isset($sess['dateid'])) {
			$this->dateid = $sess['dateid'];
		}
		
		if (isset($sess['timeid'])) {
			$this->timeid = $sess['timeid'];
		}
	}
	
	public function save() {
		$sess = $_SESSION;
		
		if ($this->dateid) {
			$sess['dateid'] = $this->dateid;
		}
		
		if ($this->timeid) {
			$sess['timeid'] = $this->timeid;
		}
	}
}