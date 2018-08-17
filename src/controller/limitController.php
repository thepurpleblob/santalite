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
        parent::__construct();
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

        // Create complicated form
        $formdates = $this->lib->format_dates($dates);
        $formtimes = $this->lib->format_times($times);
        foreach ($formdates as $date) {
             $date->times = $formtimes;
             foreach ($date->times as $time) {
                 $formid = "{$date->id}_{$time->id}";
                 $time->formlimit = $this->form->text('limit'.$formid, '', $limits[$date->id][$time->id]->maxlimit, true, null, 'number'); 
                 $time->partysize = $this->form->text('party'.$formid, '', $limits[$date->id][$time->id]->partysize, true, null, 'number');
                 $time->remaining = $limits[$date->id][$time->id]->maxlimit - $limits[$date->id][$time->id]->count;
                 $time->detail = $date->id . '/' . $time->id;
             }
        }

        // display form
        $this->View('limits', array(
            'formdates' => $formdates,
            'limits' => $limits,
            'errors' => $errors,
            'buttons' => $this->form->buttons(),
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
