<?php

namespace controller;

use core\coreController;

class adminController extends coreController {

    public function indexAction() {
        $this->require_login('organiser', $this->Url('user/index'));
        $user = $this->getUser();
        $this->View('header');
        $this->View('admin_index', array(
            'user' => $user,
        ));
        $this->View('footer');
    }

    public function cssAction() {
        echo $this->getHeaderAssets();
    }

    public function jsAction() {
        echo $this->getFooterAssets();
    }

    public function imageAction($image) {
    	global $CFG;

    	readfile($CFG->dirroot . '/assets/images/' . $image);
    }

}
