<?php
namespace Multiple\Backend\Models;
use Phalcon\Mvc\Model;

class Permission extends Model
{
    public $db;

    public function initialize()
    {
        $this->db=$this->getDi()->getShared('db');

    }
    public function getAdminPermission($id){
        $this->db=$this->getDi()->getShared('db');
        $result=$this->db->query("SELECT * FROM permission");
        $perm = $result->fetchAll();
        foreach($perm as $k=> $v){
            $result=$this->db->query("SELECT * FROM permission_admin WHERE admin_id = $id AND permission_id =".$v['id']);
            $result =  $result->fetchAll();
            if(count($result))
              $perm[$k]['pa'] = $result[0];
            else  $perm[$k]['pa'] =false;
        }

        return $perm;
    }

    public function getAdminPermissionAsKeyArray($id){
        $this->db=$this->getDi()->getShared('db');
        $result=$this->db->query("SELECT * FROM permission");
        $perm = $result->fetchAll();
        $permtmp = array();
        foreach($perm as $v){
            $result=$this->db->query("SELECT * FROM permission_admin WHERE admin_id = $id AND permission_id =".$v['id']);
            $result =  $result->fetchAll();
            $permtmp[$v['name']] = $v;
            if(count($result))
                $permtmp[$v['name']]['pa'] = $result[0];
            else  $permtmp[$v['name']]['pa'] = false;
        }

        return $permtmp;
    }

    private function savePA($admin_id,$insert,$perm_id,$field,$value){
        if (!$insert) {
            if($field == 'read')
                $sql = "INSERT INTO permission_admin (admin_id, permission_id,`read`,`edit`) VALUES($admin_id, $perm_id, $value,0)";
            else
                $sql = "INSERT INTO permission_admin (admin_id, permission_id,`read`,`edit`) VALUES($admin_id, $perm_id, 0,$value)";

        } else {
            $sql = "UPDATE permission_admin SET `$field` = $value WHERE admin_id = $admin_id AND permission_id = $perm_id  ";
        }
        $this->db=$this->getDi()->getShared('db');
        $result=$this->db->query($sql);

    }
    private function checkPA($admin_id,$perm_id){
        $this->db=$this->getDi()->getShared('db');
        $result=$this->db->query("SELECT id FROM permission_admin WHERE admin_id=$admin_id AND permission_id =$perm_id");
        return count($result->fetchAll());
    }
    public function savePermission($id,$data){
        $perms = $this->getAdminPermission($id);


        foreach($perms as $perm){
            $read = false;
            $edit = false;
            foreach($data as $k=>$v){
                $field = explode('_',$k);
                if($field[0] == $perm['id']) {
                    $$field[1] = true;
                    $this->savePA($id,$this->checkPA($id,$perm['id']),$perm['id'],$field[1],1);
                }
            }
            if(!$read)
                $this->savePA($id,$this->checkPA($id,$perm['id']),$perm['id'],'read',0);
            if(!$edit)
                $this->savePA($id,$this->checkPA($id,$perm['id']),$perm['id'],'edit',0);
        }
    }

    public function actionIsAllowed($admin_id, $controller, $action)
    {
        if($controller == 'dashboard') {
            return TRUE;
        }
        $pa = $this->getAdminPermission($admin_id);

        foreach ($pa as $v) {
            if ($v['name'] == $controller) {
                if ($action == 'index' || $action == 'search') {
                    if ($v['pa']['read'] || $v['pa']['edit']) {
                        return TRUE;
                    }
                } else {
                    if ($v['pa']['edit']) {
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
    }

}