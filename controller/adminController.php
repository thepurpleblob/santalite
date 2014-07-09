<?php

namespace controller;

use core\coreController;

class adminController extends coreController {

    function indexAction() {
        $this->View('header');
        $this->View('admin_index');
        $this->View('footer');
    }

}
