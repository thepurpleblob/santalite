<?php

namespace controller;

use core\coreController;
use model\bookingModel;

class bookingController extends coreController {

    protected $bm;

    public function __construct() {
        parent::__construct();
        $this->bm = new bookingModel;
    }

    public function startAction() {
        $this->View('header');
        $this->View('booking_start');
        $this->View('footer');
    }

    public function dateAction() {
        $cal = $this->getLib('calendar');

        //
        $cal->showMonth(11, 2014);

        $calendar = $cal->render(30, 28);
        $this->View('header');
        $this->View('booking_date', array(
            'calendar' => $calendar,
        ));
        $this->View('footer');
    }

}
