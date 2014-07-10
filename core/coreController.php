<?php

namespace core;

class coreController {
    
    protected $gump;
    
    protected $data;
    
    public function __construct() {
        require(dirname(__FILE__) . '/GUMP/gump.class.php');
        $this->gump = new GUMP();
        $this->data = $this->gump->sanitize($_POST);
    }

    /**
     * render a view 
     */
    public function View($viewname, $variables=null) {
        global $CFG;

        // extract here limits scope
        if ($variables) {
            extract($variables);
        }
        require($CFG->basepath . '/view/' . $viewname . '.php');
    }

    /**
     * Create a url from route
     */
    public function Url($route) {
        global $CFG;

        return $CFG->www . '/' . $route;
    }

}
