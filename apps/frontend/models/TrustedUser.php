<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;

class TrustedUser extends Model
{
    public $id;
    public $user_id;
    public $timestamp_x;

    public function initialize()
    {
        $this->setSource('trn_user');
    }

    public function getSource()
    {
        return 'trn_user';
    }
}