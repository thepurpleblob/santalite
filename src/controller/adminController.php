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

class adminController extends coreController {

    protected $bm;

    public function __construct() {
        parent::__construct();
        $this->bm = new bookinglib;
    }

    public function indexAction() {
        $this->require_login('organiser', $this->Url('admin/index'));
        $user = $this->getUser();
        $stats = $this->bm->getStats();
        $this->View('admin_index', array(
            'user' => $user,
            'stats' => $stats,
        ));
    }

}
