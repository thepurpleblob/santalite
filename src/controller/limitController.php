<?php
/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Limits controller
 */

namespace thepurpleblob\santa\controller;

use thepurpleblob\core\coreController;
use thepurpleblob\santa\lib\limitlib;

class limitController extends coreController {

    protected $limitlib;

    public function __construct() {
        $this->limitlib = new limitlib();
    }

    /**
     * Add or edit limits
     */
    public function indexAction() {
        $this->require_login('organiser', $this->Url('limit/index'));
        $gump = $this->getGump();
        $errors = null;

        // get times and dates
        $times = \ORM::for_table('traintime')->order_by_asc('time')->find_many();
        $dates = \ORM::for_table('traindate')->order_by_asc('date')->find_many();
        
        // get limits
        $limits = $this->limitlib->getFormLimits($dates, $times);

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
                $this->limitlib->saveForm($dates, $times, $request);
                $this->redirect($this->Url('limit/index'));
            }
            $errors = $gump->get_readable_errors();
        }

        // display form
        $this->View('limits', array(
            'dates'=>$dates,
        	'times'=>$times,
            'limits'=>$limits,
            'errors'=>$errors,
        ));
    }

    /**
     * Show details
     */
    public function detailAction($dateid, $timeid) {
        $this->require_login('organiser', $this->Url('limit/index'));

        // add up the info for this train
        $details = $this->limitlib->getDetails($dateid, $timeid);
        
        // display details
        $this->View('details', array(
                'details' => $details,
        ));
    }
}
