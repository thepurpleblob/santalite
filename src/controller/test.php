<?php

namespace controller;

use core\core_controller;

class test extends core_controller {

    function somethingAction($name) {
        $this->View('header');
        $this->View('test', array('name'=>$name));
        $this->View('footer');
    }

}
