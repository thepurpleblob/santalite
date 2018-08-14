<?php
/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * Core Controller
 */

namespace thepurpleblob\core;

class coreController {

    protected $gump;

    protected $form;

    protected $lib;

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

    public function __construct($exception=false) {
        
        // if exception handler, don't bother with this stuff
        if (!$exception) {
            $this->form = new coreForm();
            $this->extendGump();
            $this->gump = new \GUMP();
        }

        // Get the model/lib
        $this->lib = new \thepurpleblob\santa\lib\santalib($this);
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
     * @param string $viewname name of view (minus extension)
     * @param array $variables array of variables to be passed
     * @param string $menuname indicates 'selected' menu item
     * @return string
     */
    public function renderView($viewname, $variables=array(), $menuname = '') {
        global $CFG;

        // No spaces
        $viewname = trim($viewname);

        // Check cache exists
        $cachedir = $CFG->dirroot . '/cache';
        if (!is_writable($cachedir)) {
            throw new \Exception('Cache dir is not writeable ' . $cachedir);
        }

        // get controller name for menu
        if (!$menuname) {
            $reflect = new \ReflectionClass($this);
            $menuname = str_replace('Controller', '', $reflect->getShortName());
        }

        // get/setup Mustache.
        $mustache = new \Mustache_Engine(array(
            'loader' => new \Mustache_Loader_FilesystemLoader($CFG->dirroot . '/src/view'),
            'helpers' => array(
                'yesno' => function($bool) {
                    return $bool ? 'Yes' : 'No';
                },
                'path' => function($path) {
                    global $CFG;
                    return $CFG->www . '/index.php/' . $path;
                },
                'asset' => function($path) {
                    global $CFG;
                    return $CFG->www . '/src/asset/' . $path;
                },
                'active' => function($menuitem) use ($menuname) {
                    return $menuitem == $menuname ? 'active' : '';
                },
            ),
            'cache' => $cachedir,
        ));

        // Add some extra variables to context
        $user = $this->getUser();
        $system = new \stdClass();
        if ($user) {
            $system->userrole = $user->role;
            $system->admin = $user->role == 'admin';
            $system->organiser = $user->role == 'organiser';
            $system->adminpages = $system->admin || $system->organiser;
            $system->fullname = $user->firstname . ' ' . $user->lastname;
            $system->loggedin = true;
        } else {
            $system->userrole = '';
            $system->admin = false;
            $system->fullname = '';
            $system->loggedin = false;
        }
        $system->sessionid = session_id();
        $variables['system'] = $system;
        $variables['config'] = $CFG;
        $variables['www'] = $CFG->www;
        $variables['showlogin'] = (($viewname != 'user/login') && (strpos($viewname, 'booking') !== 0));
        $variables['haserrors'] = !empty($variables['errors']);


        // Get template
        $template = $mustache->loadTemplate($viewname . '.mustache');

        // and render.
        return $template->render($variables);
    }

    /**
     * Echo a view
     */
    public function View($viewname, $variables=null) {
        echo $this->renderView($viewname, $variables);

        // This should always be the last thing we do, but 
        // just in case!
        die;
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

        return $CFG->www . '/index.php/' . $route;
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

    /**
     * get session
     */
    public function getFromSession($name, $default=null) {
    	if (isset($_SESSION[$name])) {
    		return $_SESSION[$name];
    	} else {
    		if ($default) {
    			return $default;
    		} else
    			throw new \Exception("Session data for '$name' was not found");
    	}
    }

}
