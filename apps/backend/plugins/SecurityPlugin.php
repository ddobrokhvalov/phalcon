<?php
namespace Multiple\Backend\Plugins;


use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Multiple\Backend\Models\Permission;


class SecurityPlugin extends Plugin
{
    /**
     * Returns an existing or new access control list
     *
     * @returns AclList
     */
    /* public function getAcl()
     {
         if (!isset($this->persistent->acl)) {

             $acl = new AclList();

             $acl->setDefaultAction(Acl::DENY);

             //Register roles
             $roles = array(
                 'admin'  => new Role('admin'),
                 'guests' => new Role('Guests')
             );
             foreach ($roles as $role) {
                 $acl->addRole($role);
             }

             //Private area resources
             $privateResources = array(
                 'dashboard'    => array('index'),

             );
             foreach ($privateResources as $resource => $actions) {
                 $acl->addResource(new Resource($resource), $actions);
             }

             //Public area resources
             $publicResources = array(
                 'login'      => array('index'),
             );
             foreach ($publicResources as $resource => $actions) {
                 $acl->addResource(new Resource($resource), $actions);
             }

             //Grant access to public areas to both users and guests
             foreach ($roles as $role) {
                 foreach ($publicResources as $resource => $actions) {
                     foreach ($actions as $action){
                         $acl->allow($role->getName(), $resource, $action);
                     }
                 }
             }

             //Grant access to private area to role Users
             foreach ($privateResources as $resource => $actions) {
                 foreach ($actions as $action){
                     $acl->allow('admin', $resource, $action);
                 }
             }

             //The acl is stored in session, APC would be useful here too
             $this->persistent->acl = $acl;
         }

         return $this->persistent->acl;
     }*/


    private function isAllowed($admin_id, $controller, $action)
    {
        if($controller == 'dashboard')
            return true;
        $perm = new Permission();
        $pa = $perm->getAdminPermission($admin_id);

        foreach ($pa as $v) {
            if ($v['name'] == $controller) {
                if ($action == 'index' || $action == 'search') {
                    if ($v['pa']['read'] || $v['pa']['edit']) {
                        return true;
                    }
                } else {
                    if ($v['pa']['edit']) {
                        return true;
                    }
                }
            }

        }
        return false;
    }

    /**
     * This action is executed before execute any action in the application
     *
     * @param Event $event
     * @param Dispatcher $dispatcher
     * @return bool
     */
    public function beforeDispatch(Event $event, Dispatcher $dispatcher)
    {
        if ($dispatcher->getControllerName() != 'login') {
            $auth = $this->session->get('auth');
            if (!$auth) {
                $dispatcher->forward(array(
                    'controller' => 'login',
                    'action' => 'index',
                ));
            }
        }
//            $admin_id = $auth['id'];
//        }
//
//        $controller = $dispatcher->getControllerName();
//        $action = $dispatcher->getActionName();
//
//            if ($controller != 'login') {
//                //   $acl = $this->getAcl();
//                // $allowed = $acl->isAllowed($role, $controller, $action);
//
//                if ($admin_id)
//                    $allowed = $this->isAllowed($admin_id, $controller, $action);
//                /*if (!$allowed) {
//                    var_dump($allowed, $controller, $action);
//                    die('');
//                }*/
//                if (!$allowed) {
//                    $dispatcher->forward(array(
//                        'controller' => 'login',
//                        'action' => 'index'
//                    ));
//                    if ($auth)
//                        $this->session->destroy();
//                    return false;
//                }
//            }

    }
}