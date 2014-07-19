<?php

namespace controller;

use core\coreController;
use model\faresModel;

class faresController extends coreController {
    
    /**
     * Add or edit time
     */
    public function indexAction() {
        $fm = new faresModel();
        $gump = $this->getGump();
        $errors = null;
        
        // get fares object
        if (!$fares = $fm->getFares()) {
            throw new \Exception('Fares record is missing or does not have id=1');
        }
        
        // process data
        if ($request = $this->getRequest()) {
            if (!empty($request['cancel'])) {
                $this->redirect($this->Url('admin/index'));
            }
            
            $gump->validation_rules(array(
                'adult' => 'required|numeric', 
                'child' => 'required|numeric',
            ));
            if ($validated_data = $gump->run($request)) {
                $fares->adult = round($request['adult'] * 100, 0);
                $fares->child = round($request['child'] * 100, 0);
                $fm->updateFares($fares);
                $this->redirect($this->Url('admin/index'));
            }
            $errors = $gump->get_readable_errors();
        }
 
        // display form
        $this->View('header');
        $this->View('fares_edit', array(
            'fares'=>$fares,
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
