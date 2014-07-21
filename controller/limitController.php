<?php

namespace controller;

use core\coreController;
use model\limitModel;

class limitController extends coreController {

    /**
     * Add or edit limits
     */
    public function indexAction() {
        $this->require_login('organiser', $this->Url('limit/index'));
        $lm =  new limitModel();
        $gump = $this->getGump();
        $errors = null;

        // get times and dates
        $times = \ORM::for_table('traintime')->order_by_asc('time')->find_many();
        $dates = \ORM::for_table('traindate')->order_by_asc('date')->find_many();
        
        // get limits
        $limits = $lm->getFormLimits($dates, $times);

        // process data
        if ($request = $this->getRequest()) {
            if (!empty($request['cancel'])) {
                $this->redirect($this->Url('admin/index'));
            }

            // Loop through possibilities to validate
            $rules = array();
            foreach ($dates as $date) {
                foreach ($times as $time) {
                    $index = "{$date->id}_{$time->id}";
                    $rules['limit'.$index] = 'required|integer';
                    $rules['party'.$index] = 'required|integer';
                }
            }
            $gump->validation_rules($rules);
            
            if ($validated_data = $gump->run($request)) {
                $lm->saveForm($dates, $times, $request);
                $this->redirect($this->Url('limit/index'));
            }
            $errors = $gump->get_readable_errors();
        }

        // display form
        $this->View('header');
        $this->View('limits', array(
            'dates'=>$dates,
        	'times'=>$times,
            'limits'=>$limits,
            'errors'=>$errors,
        ));
        $this->View('footer');
    }

}
