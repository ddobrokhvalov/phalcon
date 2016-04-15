?php
namespace Multiple\Frontend\Controllers;

use Phalcon\Mvc\Controller;
use Multiple\Frontend\Models\Applicant;
use Multiple\Library\TrustedLibrary;
use Phalcon\Di;

class EsignController extends ControllerBase{
    public function indexAction(){
        ini_set('display_errors', true);
        var_dump('65765756');
        echo "4654654645";
        //TrustedLibrary::trusted_esign();
    }

    public function ajaxAction(){
        ini_set('display_errors', true);
        var_dump('65765756');
        echo "4654654645";
        //TrustedLibrary::trusted_esign();
    }    
}