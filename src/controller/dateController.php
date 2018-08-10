<?php
/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Dates admin controller
 */

namespace thepurpleblob\santa\controller;

use thepurpleblob\core\coreController;

class dateController extends coreController {

    public function indexAction() {
        $this->require_login('admin', $this->Url('date/index'));
        $dates = \ORM::for_table('traindate')->order_by_asc('date')->find_many();
        $this->View('date_index', array(
            'dates' => $this->lib->format_dates($dates),
            'isdates' => !empty($dates),
        ));
    }
    
    /**
     * Add or edit time
     */
    public function editAction($dateid) {
        $this->require_login('organiser', $this->Url('date/index'));
        $gump = $this->getGump();
        $errors = null;
        
        // get time object
        if (!$dateid) {
            $date = \ORM::for_table('traindate')->create();
            $date->id = 0;
            $date->date = $this->lib->get_default_date();
        } else {
            $date = \ORM::for_table('traindate')->find_one($dateid);
        }
        
        // process data
        if ($request = $this->getRequest()) {
            if (!empty($request['cancel'])) {
                $this->redirect($this->Url('date/index'));
            }
            
            // date validation is weird
            //$request['date'] = str_replace('/', '-', $request['date']);
            
            $gump->validation_rules(array(
                'date' => 'required|time',                
            ));
            if ($validated_data = $gump->run($request)) {
                $unixtime = strtotime($request['date']); 
                $date->date = $unixtime;
                $date->save();
                $this->redirect($this->Url('date/index'));
            }
            $errors = $gump->get_readable_errors();
        }

        // Create form.
        $form = new \stdClass;
        $form->date = $this->form->date('date', 'Date', $date->date, true);
        $form->buttons = $this->form->buttons();
 
        // display form
        $this->View('date_edit', array(
            'date' => $date,
            'newdate' => $date->id == 0,
            'form' => $form,
            'errors' => $errors,
        ));
    }
    
    /**
     * Show delete warning
     */
    public function deleteAction($dateid) {
        $this->require_login('admin', $this->Url('date/index'));
        $this->View('datetime_delete', array(
            'confirmurl' => $this->Url('date/confirm/'.$dateid),
            'cancelurl' => $this->Url('date/index'),
        ));
    }
    
    /**
     * Confirm delete warning
     */
    public function confirmAction($dateid) {
        $this->require_login('organiser', $this->Url('date/index'));
        $date = \ORM::for_table('traindate')->find_one($dateid);
        $date->delete();
        $this->redirect($this->Url('date/index'));
    }
}
