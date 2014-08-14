<?php

namespace controller;

use core\coreController;

class adminController extends coreController {

    public function indexAction() {
        $this->require_login('organiser', $this->Url('admin/index'));
        $user = $this->getUser();
        $this->View('header');
        $this->View('admin_index', array(
            'user' => $user,
        ));
        $this->View('footer');
    }

    public function cssAction() {
        echo $this->getHeaderAssets();
    }

    public function jsAction() {
        echo $this->getFooterAssets();
    }

    public function imageAction($image) {
    	global $CFG;
    	
    	$file_extension = strtolower(substr(strrchr($image,"."),1));
    	
    	switch( $file_extension ) {
    	    case "gif": $ctype="image/gif"; break;
    	    case "png": $ctype="image/png"; break;
    	    case "jpeg":
    	    case "jpg": $ctype="image/jpg"; break;
    	    default:
    	}
    	
    	header('Content-type: ' . $ctype);

    	readfile($CFG->dirroot . '/assets/images/' . $image);
    }

}
