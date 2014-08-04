<?php

namespace controller;

use core\coreController;
use model\installModel;

class installController extends coreController {

    function installAction() {
        //$this->require_login('admin', $this->Url('install/install'));
        $installer = new installModel();
        $installer->install_tables();
        $this->View('header');
        $this->View('complete');
        $this->View('footer');
    }

}
