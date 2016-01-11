<?php
namespace Multiple\Backend\Controllers;
use Multiple\Backend\Models\Admin;
use Multiple\Backend\Models\Permission;

class PermissionController extends ControllerBase
{

    public function editAction($id)
    {
        $admin = Admin::findFirstById($id);
        if (!$admin) {
            $this->flash->error("Admin was not found");
            return $this->forward("admins/index");
        }
        $perm = new Permission();
        $this->view->admin = $admin;
        $this->view->permission =  $perm->getAdminPermission($id);

    }
    public function saveAction($id)
    {
        $admin = Admin::findFirstById($id);
        if (!$admin) {
            $this->flash->error("Admin was not found");
            return $this->forward("admins/index");
        }
        $data = $this->request->getPost();

            $perm = new Permission();
            $perm->savePermission($id,$data);
            return $this->forward("admins/index");

    }
}