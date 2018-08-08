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

    public function getHeaderAssets() {
        global $CFG;

        $css = new AssetCollection(array(
            new HttpAsset('//maxcdn.bootstrapcdn.com/bootswatch/3.2.0/cerulean/bootstrap.min.css'),
            //new HttpAsset('//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css'),
            new GlobAsset($CFG->dirroot . '/assets/css/*'),
        ));

        return $css->dump();
    }

    public function getFooterAssets() {
        global $CFG;

        $js = new AssetCollection(array(
            new HttpAsset('//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js'),
            new HttpAsset('//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js'),
            new GlobAsset($CFG->dirroot . '/assets/js/*'),
        ));

        return $js->dump();
    }

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
     * @return string
     */
    public function renderView($viewname, $variables=array()) {
        global $CFG;

        // No spaces
        $viewname = trim($viewname);

        // Check cache exists
        $cachedir = $CFG->dirroot . '/cache';
        if (!is_writable($cachedir)) {
            throw new \Exception('Cache dir is not writeable ' . $cachedir);
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
                }
            ),
            'cache' => $cachedir,
        ));

        // Add some extra variables to array
        $user = $this->getUser();
        $system = new \stdClass();
        if ($user) {
            $system->userrole = $user->role;
            $system->admin = $user->role == 'ROLE_ADMIN';
            $system->organiser = $user->role == 'ROLE_ORGANISER';
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
     * render a view
     */
    public function View($viewname, $variables=null) {
        echo $this->renderView($viewname, $variables);
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
