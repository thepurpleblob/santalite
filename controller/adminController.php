<?php

namespace controller;

use core\coreController;

class adminController extends coreController {

    function indexAction() {
        $this->require_login('organiser', $this->Url('user/index'));
        $user = $this->getUser();
        $this->View('header');
        $this->View('admin_index', array(
            'user' => $user,
        ));
        $this->View('footer');
    }

}
