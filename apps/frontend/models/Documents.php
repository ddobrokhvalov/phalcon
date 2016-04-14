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
}