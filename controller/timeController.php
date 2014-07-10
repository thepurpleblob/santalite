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
    
    /**
     * Add or edit time
     */
    function editAction($timeid) {
        $tm = new timeModel();
        if (!$timeid) {
            $time = new \stdClass();
        } else {
            // read existing time from db
        }
        
        // display form
        $this->View('header');
        $this->View('time_edit', array('timeid'=>$timeid));
        $this->View('footer');       
    }

}
