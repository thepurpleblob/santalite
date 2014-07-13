<?php

namespace core;

class coreController {
    
    protected $gump;
    
    protected $form;
    
    private function extendGump() {
        
        // valid time
        \GUMP::add_validator("time", function($field, $input, $param=null) {
            return strtotime($input[$field]) !== false;
        });
    }
    
    public function __construct() {
        $this->form = new coreForm();  
        require_once(dirname(__FILE__) . '/GUMP/gump.class.php');
        $this->extendGump();
        $this->gump = new \GUMP();
    }
    
    /**
     * Get GUMP (form verification software)
     */
    public function getGump() {
        return $this->gump;
    }
    
    /**
     * Get request data
     */
    public function getRequest() {
        if (empty($_POST)) {
            return false;
        } else {
            return $this->gump->sanitize($_POST);
        }    
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
        
        // also need the form class in scope
        $form = $this->form;
        
        require($CFG->basepath . '/view/' . $viewname . '.php');
    }
    
    /**
     * Display form errors
     * 
     */
    public function formErrors($errors) {
        echo '<div class="alert alert-danger">';
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul></div>";
    }

    /**
     * Create a url from route
     */
    public function Url($route) {
        global $CFG;

        return $CFG->www . '/' . $route;
    }
    
    /**
     * Redirect to some other location
     */
    function redirect($url) {
        header("Location: $url");
        die;
    }

}
