<?php

namespace controller;

use core\coreController;

class bookingController extends coreController {

    public function startAction() {
        $this->View('header');
        $this->View('booking_start');
        $this->View('footer');
    }
    
    /**
     * Add or edit time
     */
    public function editAction($timeid) {
        $this->require_login('organiser', $this->Url('time/index'));
        $gump = $this->getGump();
        $errors = null;
        
        // get time object
        if (!$timeid) {
            $time = \ORM::for_table('traintime')->create();
            $time->id = 0;
            $time->time = time();
        } else {
            $time = \ORM::for_table('traintime')->find_one($timeid);
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
                $time->save();
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
        $this->require_login('organiser', $this->Url('time/index'));
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
        $this->require_login('organiser', $this->Url('time/index'));
        $time = \ORM::for_table('traintime')->find_one($timeid);
        $time->delete();
        $this->redirect($this->Url('time/index'));
    }    

}
