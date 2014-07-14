<?php

namespace controller;

use core\coreController;
use model\timeModel;

class timeController extends coreController {

    public function indexAction() {
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
    public function editAction($timeid) {
        $tm = new timeModel();
        $gump = $this->getGump();
        $errors = null;
        
        // get time object
        if (!$timeid) {
            $time = new \stdClass();
            $time->id = 0;
            $time->time = time();
        } else {
            $time = $tm->getTime($timeid);
        }
        
        // process data
        if ($request = $this->getRequest()) {
            if (!empty($request['cancel'])) {
                $this->redirect($this->Url('time/index'));
            }
            
            $gump->validation_rules(array(
                'time' => 'required|time',                
            ));
            if ($validated_data = $gump->run($request)) {
                $unixtime = strtotime($request['time']); 
                $time->time = $unixtime;
                $tm->updateTime($time);
                $this->redirect($this->Url('time/index'));
            }
            $errors = $gump->get_readable_errors();
        }
 
        // display form
        $this->View('header');
        $this->View('time_edit', array(
            'time'=>$time,
            'errors'=>$errors,
        ));
        $this->View('footer');       
    }
    
    
    /**
     * Show delete warning
     */
    public function deleteAction($timeid) {
        $this->View('header');
        $this->View('datetime_delete', array(
            'confirmurl' => $this->Url('time/confirm/'.$timeid),
            'cancelurl' => $this->Url('time/index'),
        ));
        $this->View('footer');
    }
    
    /**
     * Confirm delete warning
     */
    public function confirmAction($timeid) {
        $tm = new timeModel();
        $tm->deleteTime($timeid);
        $this->redirect($this->Url('time/index'));
    }    

}
