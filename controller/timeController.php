<?php

namespace controller;

use core\coreController;
use model\timeModel;

class timeController extends coreController {

    function indexAction() {
        $tm = new timeModel;
        $times = $tm->getAllTimes();
    //echo "<pre>"; var_dump($times); die;
        $this->View('header');
        $this->View('time_index', array('times'=>$times));
        $this->View('footer');
    }

}
