<?php
namespace Multiple\Backend\Controllers;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Multiple\Backend\Models\Category;
use Multiple\Backend\Models\Template;


class TemplateController extends ControllerBase
{

    public function indexAction()
    {
        $cat_id = $this->request->get("cat_id");
        $templates = false;
        $currentCategory = false;
        $templ = new Template();
        if($cat_id) {
            $templates = $templ->findByCategoryId($cat_id);
            $currentCategory = Category::findFirst($cat_id);
        }

        $categorys = Category::find();
        $this->view->categorys = $categorys;
        $this->view->templates = $templates;
        $this->view->currentCategory = $currentCategory;
        $this->setMenu();
    }
}