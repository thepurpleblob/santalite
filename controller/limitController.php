<?php

namespace controller;

use core\coreController;
use model\limitModel;
use model\dateModel;
use model\timeModel;

class limitController extends coreController {

    /**
     * Add or edit limits
     */
    public function indexAction() {
        $tm = new timeModel();
        $dm = new dateModel();
        $lm = new limitModel();
        $gump = $this->getGump();
        $errors = null;

        // get times and dates
        $times = $tm->getAllTimes();
        $dates = $dm->getAllDates();
        
        // get limits
        $limits = $lm->getFormLimits($dates, $times);

        // process data
        if ($request = $this->getRequest()) {
            if (!empty($request['cancel'])) {
                $this->redirect($this->Url('date/index'));
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
