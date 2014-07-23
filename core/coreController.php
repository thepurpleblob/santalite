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
        
        // valid role
        \GUMP::add_validator('role', function($field, $input, $param=null) {
            $role = $input[$field];
            return ($role=='admin') || ($role=='organiser');    
        });
        
        // valid password
        \GUMP::add_validator('password', function($field, $input, $param=null) {
            $password = $input['password'];
            $username = $input['username'];
            $user = \ORM::for_table('user')->where(array(
                'username' => $username,
                'password' => md5($password),
                ))->find_one();
            return !($user === false);
        });
    }
    
    /**
     * Instantiate class in library
     * @param type $name
     */
    public function getLib($name) {
        $namespace = 'lib';
        $classname = $namespace . '\\' . $name;
        return new $classname;
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
    public function redirect($url) {
        header("Location: $url");
        die;
    }
    
    /**
     * Check for login/security
     */
    public function require_login($role, $wantsurl) {
        if (!empty($_SESSION['user'])) {
            $user = $_SESSION['user'];
            if ($role=='admin') {
                if ($user->role == 'admin') {
                    return true;
                } else {
                    $this->redirect($this->Url('user/roleerror'));
                }
            } else {
                return true;
            }
        }
        
        $_SESSION['wantsurl'] = $wantsurl;
        $this->redirect($this->Url('user/login'));
    }
    
    /**
     * Get logged in user
     */
    public function getUser() {
        if (!empty($_SESSION['user'])) {
            return $_SESSION['user'];
        } else {
            return false;
        }
    }

}
