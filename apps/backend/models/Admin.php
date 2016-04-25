<?php
namespace Multiple\Backend\Models;
use Phalcon\Mvc\Model;
use  Multiple\Library\PHPImageWorkshop\ImageWorkshop;

class Admin extends Model
{
    public $id;
    public $email;
    public $password;
    public $surname;
    public $name;
    public $patronymic;
    public $avatar;

    public function initialize()
    {
        $this->setSource("admin");
        //$this->skipAttributesOnCreate(array('avatar','surname','name','patronymic'));
        //$this->skipAttributesOnUpdate(array('avatar','surname','name','patronymic'));
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