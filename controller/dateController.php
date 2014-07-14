<?php

namespace controller;

use core\coreController;
use model\dateModel;

class dateController extends coreController {

    public function indexAction() {
        $tm = new dateModel;
        $dates = $tm->getAllDates();
        $this->View('header');
        $this->View('date_index', array('dates'=>$dates));
        $this->View('footer');
    }
    
    /**
     * Add or edit time
     */
    public function editAction($dateid) {
        $tm = new dateModel();
        $gump = $this->getGump();
        $errors = null;
        
        // get time object
        if (!$dateid) {
            $date = new \stdClass();
            $date->id = 0;
            $date->date = time();
        } else {
            $date = $tm->getDate($dateid);
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
                $tm->updateDate($date);
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
        $tm = new dateModel();
        $tm->deleteDate($dateid);
        $this->redirect($this->Url('date/index'));
    }
}
