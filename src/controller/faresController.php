<?php
/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Fares admin controller
 */

namespace thepurpleblob\santa\controller;

use thepurpleblob\core\coreController;

class faresController extends coreController {
    
    /**
     * Add or edit time
     */
    public function indexAction() {
        $this->require_login('admin', $this->Url('fares/index'));
        $gump = $this->getGump();
        $errors = null;
        
        // get fares object
        if (!$fares = \ORM::for_table('fares')->find_one(1)) {
            throw new \Exception('Fares record is missing or does not have id=1');
        }
        
        // process data
        if ($request = $this->getRequest()) {
            if (!empty($request['cancel'])) {
                $this->redirect($this->Url('fares/index'));
            }
            
            $gump->validation_rules(array(
                'adult' => 'required|numeric', 
                'child' => 'required|numeric',
            ));
            if ($validated_data = $gump->run($request)) {
                $fares->adult = round($request['adult'] * 100, 0);
                $fares->child = round($request['child'] * 100, 0);
                $fares->save();
                $this->redirect($this->Url('fares/index'));
            }
            $errors = $gump->get_readable_errors();
        }

        // Create form
        $form = new \stdClass;
        $form->adult = $this->form->text('adult', 'Adult fare (&pound;)', number_format($fares->adult / 100, 2), true, null, 'number');
        $form->child = $this->form->text('child', 'Child fare (&pound;)', number_format($fares->child / 100, 2), true, null, 'number');
        $form->buttons = $this->form->buttons();
 
        // display form
        $this->View('fares_edit', array(
            'fares'=> $fares,
            'form' => $form,
            'errors'=> $errors,
        ));
    }
    
}
