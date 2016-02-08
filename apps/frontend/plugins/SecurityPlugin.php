<?php
namespace Multiple\Frontend\Plugins;


use Phalcon\Acl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Acl\Adapter\Memory as AclList;


class SecurityPlugin extends Plugin
{
    /**
     * Returns an existing or new access control list
     *
     * @returns AclList
     */
    public function getAcl()
    {
        if (!isset($this->persistent->acl)) {

            $acl = new AclList();

            $acl->setDefaultAction(Acl::DENY);

            //Register roles
            $roles = array(
                'user' => new Role('user'),
                'guests' => new Role('Guests')
            );
            foreach ($roles as $role) {
                $acl->addRole($role);
            }
            $privateResources = array(
                'complaint' => array('index','edit','add','save','delete'),
                'applicant' => array('index','edit','add','save','delete')

            );
            foreach ($privateResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }

            //Public area resources
            $publicResources = array(
                'login' => array('index', 'start'),
                'index' => array('index'),
            );
            foreach ($publicResources as $resource => $actions) {
                $acl->addResource(new Resource($resource), $actions);
            }

            //Grant access to public areas to both users and guests
            foreach ($roles as $role) {
                foreach ($publicResources as $resource => $actions) {
                    foreach ($actions as $action) {
                        $acl->allow($role->getName(), $resource, $action);
                    }
                }
            }

            foreach ($privateResources as $resource => $actions) {
                foreach ($actions as $action) {
                    $acl->allow('user', $resource, $action);
                }
            }

            //The acl is stored in session, APC would be useful here too
            $this->persistent->acl = $acl;
        }

        return $this->persistent->acl;
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


        $auth = $this->session->get('auth');
        if (!$auth) {
            $role = 'Guests';
        } else {
            $role = 'user';
        }

        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();


        $acl = $this->getAcl();
        $allowed = $acl->isAllowed($role, $controller, $action);

     /*   if (!$allowed) {
            $dispatcher->forward(array(
                'controller' => 'login',
                'action' => 'index'
            ));
            if ($auth)
                $this->session->destroy();
            return false;
        } */
    }
}