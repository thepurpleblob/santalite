<?php
/**
 * SRPS Santa Booking
 *
 * Copyright 2018, Howard Miller (howardsmiller@gmail.com)
 *
 * User controller
 */

namespace thepurpleblob\santa\controller;

use thepurpleblob\core\coreController;

class userController extends coreController {
    
    public function indexAction() {
        $this->require_login('admin', $this->Url('user/index'));
        $users = \ORM::for_table('user')->find_many();
        $formattedusers = $this->lib->format_users($users);
        $this->View('user_index', array(
            'users'=>$formattedusers
        ));
    }
    
    /**
     * Add or edit time
     */
    public function editAction($userid) {
        $this->require_login('admin', $this->Url('user/index'));
        $gump = $this->getGump();
        $errors = null;
        
        // possible roles
        $roles = array(
            'admin' => 'Administrator',
            'organiser' => 'Organiser',
        );
        
        // get user object
        if (!$userid) {
            $user = \ORM::for_table('user')->create();
            $user->role = 'organiser';
        } else {
            $user = \ORM::for_table('user')->find_one($userid);
        }

        // Can't edit admin username
        $isadmin = $user->username == 'admin';
        
        // process data
        if ($request = $this->getRequest()) {
            if (!empty($request['cancel'])) {
                $this->redirect($this->Url('user/index'));
            }
            
            $gump->validation_rules(array(
                'username' => 'required',  
                'fullname' => 'required',
                'role' => 'required|role'
            ));
            if ($validated_data = $gump->run($request)) {
                if (!$isadmin) {
                    $user->username = $request['username'];
                }
                if ($request['password']) {
                    $user->password = password_hash($request['password'], PASSWORD_DEFAULT);
                }    
                $user->fullname = $request['fullname'];
                $user->role = $request['role'];
                $user->save();
                $this->redirect($this->Url('user/index'));
            }
            $errors = $gump->get_readable_errors();
        }

        // Create form
        $form = new \stdClass;
        $form->username = $this->form->text('username', 'Username', $user->username, true, null, 'text', $isadmin);
        $form->fullname = $this->form->text('fullname', 'Full name', $user->fullname, true);
        $form->password = $this->form->password('password', 'Password');
        $form->role = $this->form->select('role', 'User role', $user->role, $roles);
        $form->buttons = $this->form->buttons();
 
        // display form
        $this->View('user_edit', array(
            'newuser' => $userid == 0,
            'user' => $user,
            'form' => $form,
            'errors' => $errors,
        ));
    }
    
    /**
     * Show delete warning
     */
    public function deleteAction($userid) {
        $this->require_login('admin', $this->Url('user/index'));
        
        // User cannot be admin (and must exist)
        $user = \ORM::for_table('user')->find_one($userid);
        if (!$user || ($user->username == 'admin')) {
            $this->redirect($this->Url('user/index'));
        }

        $this->View('user_delete', array(
            'user' => $user,
            'confirmurl' => $this->Url('user/confirm/'.$userid),
            'cancelurl' => $this->Url('user/index'),
        ));
    }
    
    /**
     * Confirm delete warning
     */
    public function confirmAction($userid) {
        $this->require_login('admin', $this->Url('user/index'));

        // User cannot be admin (and must exist)
        $user = \ORM::for_table('user')->find_one($userid);
        if (!$user || ($user->username == 'admin')) {
            $this->redirect($this->Url('user/index'));
        }

        $user->delete();
        $this->redirect($this->Url('user/index'));
    }
    
    /**
     * Handle login page
     */
    public function loginAction() {
        $errors = array();
        $gump = $this->getGump();
        unset($_SESSION['user']);
        
        // process data
        if ($request = $this->getRequest()) {
            if (!empty($request['cancel'])) {
                unset($_SESSION['wantsurl']);
                $this->redirect($this->Url('user/login'));
            }
            
            $gump->validation_rules(array(
                'username' => 'required',  
                'password' => 'required',
            ));
            if ($validated_data = $gump->run($request)) {
                $username = $request['username'];
                $password = $request['password'];
                if ($user = \ORM::for_table('user')->where(array('username'=>$username))->find_one()) {
                    if (password_verify($password, $user->password)) { 
                        if (password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
                            $user->password = password_hash($password, PASSWORD_DEFAULT);
                            $user->save();
                        }
                        $_SESSION['user'] = $user;
                        if (!empty($_SESSION['wantsurl'])) {
                            $url = $_SESSION['wantsurl'];
                        } else{
                            $url = $this->Url('admin/index');
                        }
                        $this->redirect($url);
                    }
                }
            }
            $errors = ['Invalid login'];
        }

        // Create login form
        $form = new \stdClass;
        $form->username = $this->form->text('username', 'Username', '');
        $form->password = $this->form->password('password', 'Password');
        
        $this->View('login', array(
            'errors' => $errors,
            'form' => $form,
        ));
    }
    
    /**
     * Handle logout
     * 
     */
    public function logoutAction() {
        unset($_SESSION['user']);
        unset($_SESSION['wantsurl']);
        $this->redirect($this->Url('admin/index'));
    }
    
    /**
     * Handle role error
     */
    public function roleerrorAction() {
        $this->View('role_error');
    }
}
