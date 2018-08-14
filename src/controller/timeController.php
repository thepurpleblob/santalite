<?php
/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Time controller
 */

namespace thepurpleblob\santa\controller;

use thepurpleblob\core\coreController;

class timeController extends coreController {

    public function indexAction() {
        $this->require_login('admin', $this->Url('time/index'));
        $times = \ORM::for_table('traintime')->order_by_asc('time')->find_many();
        $this->View('time_index', array(
            'times' => $this->lib->format_times($times),
            'istimes' => !empty($times),
        ));
    }
    
    /**
     * Add or edit time
     */
    public function editAction($timeid) {
        $this->require_login('admin', $this->Url('time/index'));
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
                $time->time = $request['time'];
                $time->save();
                $this->redirect($this->Url('time/index'));
            }
            $errors = $gump->get_readable_errors();
        }

        // Form
        $form = new \stdClass;
        $form->time = $this->form->time('time', 'Service time', $time->time);
        $form->buttons = $this->form->buttons();
 
        // display form
        $this->View('time_edit', array(
            'newtime' => $timeid == 0,
            'time' => $time,
            'form' => $form,
            'errors' => $errors,
        ));
    }
    
    
    /**
     * Show delete warning
     */
    public function deleteAction($timeid) {
        $this->require_login('admin', $this->Url('time/index'));
        $this->View('datetime_delete', array(
            'confirmurl' => $this->Url('time/confirm/'.$timeid),
            'cancelurl' => $this->Url('time/index'),
        ));
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
