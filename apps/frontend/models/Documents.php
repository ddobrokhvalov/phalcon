<<<<<<< HEAD
<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;

class Documents extends Model
{
    public $ID;
    public $TYPE;

    public function initialize()
    {
        $this->setSource("trn_documents");
    }

    public function getSource()
    {
        return "trn_documents";
    }
=======
<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;

class Documents extends Model
{
    public $ID;
    public $TYPE;

    public function initialize()
    {
        $this->setSource("trn_documents");
    }

    public function getSource()
    {
        return "trn_documents";
    }
>>>>>>> dd04a6e757cec51eeb995741aed37dd7b2643b87
}