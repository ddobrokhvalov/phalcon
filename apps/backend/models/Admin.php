<?php
namespace Multiple\Backend\Models;
use Phalcon\Mvc\Model;
use Multiple\Backend\Models\Permission;
use Multiple\Library\PHPImageWorkshop\ImageWorkshop;

class Admin extends Model
{
    public $id;
    public $email;
    public $password;
    public $surname;
    public $name;
    public $patronymic;
    public $avatar;

    public function initialize(){
        $this->setSource("admin");
        $this->allowEmptyStringValues(['surname', 'name', 'patronymic', 'avatar', 'phone']);
    }
    public function getSource()
    {
        return "admin";
    }
    public function getId()
    {
        return $this->id;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function getFullName(){
        return $this->surname.' '.$this->name.' '.$this->patronymic;
    }
    public function getSurnameAndInitials(){
        return $this->surname.' '.substr($this->name,0,1).'.'.substr($this->patronymic,0,1).'.';
    }
    public function saveAvatar($avatar){
        $baseLocation = 'files/avatars/';
        $allowedFormats = ['image/jpeg', 'image/png', 'image/gif'];
        if(count($avatar)){
            if(in_array($avatar[0]->getType(), $allowedFormats)) {
                $filename = md5(date('Y-m-d H:i:s:u')) .'.'. $avatar[0]->getExtension();
                if ($avatar[0]->moveTo($baseLocation . $filename)) {
                    $this->avatar = $filename;
                    return true;
                }
            }
        }
        return false;
    }

    public function getPermissions(){
        $permission = new Permission();
        $rights = $permission->getAdminPermissionAsKeyArray($this->id);
        $rules = array(
            '0' => 'user',
            '1' => 'complaints',
            '2' => 'lawyer',
            '3' => 'arguments',
            '4' => 'template',
        );
        $string_rights = array();
        foreach ($rules as $rule) {
            if (isset($rights["{$rule}"]['pa']['read']) && $rights["{$rule}"]['pa']['read'] == 1 ||
            isset($rights["{$rule}"]['pa']['edit']) && $rights["{$rule}"]['pa']['edit'] == 1) {
                $string_rights[] =  $this->getStringRightValue($rule);
            }
        }
        return implode(', ', array_unique($string_rights));
    }
    
    public function getStringRightValue($rule) {
        switch ($rule) {
            case 'user':
                return 'пользователи';
            case 'complaints':
                return 'жалобы';
            default: return 'ответы';
        }
    }
    /*public function beforeCreate(){
        parent::beforeCreate();
        $metaData = $this->getModelsMetaData();
        $attributes = $metaData->getNotNullAttributes($this);
        var_dump($attributes);
        foreach($attributes as $field) {
            if(!isset($this->{$field}) || is_null($this->{$field})) {
                $this->{$field} = new RawValue('default');
            }
        }
        die();
    }*/
}