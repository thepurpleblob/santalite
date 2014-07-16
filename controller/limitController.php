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
        $this->View('limits', array(
            'dates'=>$dates,
        	'times'=>$times,
            'errors'=>$errors,
        ));
        $this->View('footer');
    }

}
