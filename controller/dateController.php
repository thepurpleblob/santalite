<?php

namespace controller;

use core\coreController;

class dateController extends coreController {

    public function indexAction() {
        $this->require_login('organiser', $this->Url('date/index'));
        $dates = \ORM::for_table('traindate')->order_by_asc('date')->find_many();
        $this->View('header');
        $this->View('date_index', array('dates'=>$dates));
        $this->View('footer');
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
            $date->date = time();
        } else {
            $date = \ORM::for_table('traindate')->find_one($dateid);
        }
        
        // process data
        if ($request = $this->getRequest()) {
            if (!empty($request['cancel'])) {
                $this->redirect($this->Url('date/index'));
            }
            
            // date validation is weird
            $request['date'] = str_replace('/', '-', $request['date']);
            
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
 
        // display form
        $this->View('header');
        $this->View('date_edit', array(
            'date'=>$date,
            'errors'=>$errors,
        ));
        $this->View('footer');       
    }
    
    /**
     * Show delete warning
     */
    public function deleteAction($dateid) {
        $this->require_login('organiser', $this->Url('date/index'));
        $this->View('header');
        $this->View('datetime_delete', array(
            'confirmurl' => $this->Url('date/confirm/'.$dateid),
            'cancelurl' => $this->Url('date/index'),
        ));
        $this->View('footer');
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
