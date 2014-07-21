<?php

namespace controller;

use core\coreController;

class userController extends coreController {
    
    public function indexAction() {
        $this->require_login('admin', $this->Url('user/index'));
        $users = \ORM::for_table('user')->find_many();
        $this->View('header');
        $this->View('user_index', array('users'=>$users));
        $this->View('footer');
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
                $user->username = $request['username'];
                if ($request['password']) {
                    $user->password = md5($request['password']);
                }    
                $user->fullname = $request['fullname'];
                $user->role = $request['role'];
                $user->save();
                $this->redirect($this->Url('user/index'));
            }
            $errors = $gump->get_readable_errors();
        }
 
        // display form
        $this->View('header');
        $this->View('user_edit', array(
            'user'=>$user,
            'roles'=>$roles,
            'errors'=>$errors,
        ));
        $this->View('footer');       
    }
    
    /**
     * Show delete warning
     */
    public function deleteAction($userid) {
        $this->require_login('admin', $this->Url('user/index'));
        $this->View('header');
        $this->View('user_delete', array(
            'confirmurl' => $this->Url('user/confirm/'.$userid),
            'cancelurl' => $this->Url('user/index'),
        ));
        $this->View('footer');
    }
    
    /**
     * Confirm delete warning
     */
    public function confirmAction($userid) {
        $this->require_login('admin', $this->Url('user/index'));
        $user = \ORM::for_table('user')->find_one($userid);
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
                'password' => 'required|password',
            ));
            if ($validated_data = $gump->run($request)) {
                $username = $request['username'];
                $user = \ORM::for_table('user')->where(array('username'=>$username))->find_one();
                $_SESSION['user'] = $user;
                if (!empty($_SESSION['wantsurl'])) {
                    $url = $_SESSION['wantsurl'];
                } else{
                    $url = $this->Url('admin/index');
                }
                $this->redirect($url);
            }
            $errors = $gump->get_readable_errors();
        }
        
        $this->View('header');
        $this->View('login', array(
            'errors' => $errors,
        ));
        $this->View('footer');
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
        $this->View('header');
        $this->View('role_error');
        $this->View('footer');
    }
}
