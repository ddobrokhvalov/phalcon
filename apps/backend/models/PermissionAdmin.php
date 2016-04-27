<?php
namespace Multiple\Backend\Models;
use Phalcon\Mvc\Model;

class PermissionAdmin extends Model
{
    public $id;
    public $admin_id;
    public $permission_id;
    public $read;
    public $edit;
    public function initialize()
    {
        $this->setSource("permission_admin");
        $this->allowEmptyStringValues(['read', 'edit']);
    }

    public function getSource()
    {
        return "permission_admin";
    }
}