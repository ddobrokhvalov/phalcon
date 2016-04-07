<?php
namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\Applicant;
use Multiple\Library\TrustedLibrary;

class AjaxController extends Controller{

    public function trustedAction(){
        TrustedLibrary::trusted_esign();
    }
}