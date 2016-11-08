<?php

namespace Multiple\Frontend\Controllers;

class HelpController extends ControllerBase
{
    private $arrMenu = array(
        'FAQ по пользованию сервисом' => '/help/index',
        'Часто задаваемые вопросы' => '/help/question',
        'Нормативные документы' => '/help/documents',
        'Сроки в 44-Ф3' => '/help/terms44',
        'Подведомственность' => '/help/jurisdiction',
        'Спорные случаи' => '/help/contrcases',
        'Штрафы' => '/help/penalty',
        'Типичные доводы жалоб' => '/help/arguments',
    );


    public function indexAction()
    {
        $this->setMenu();
        $this->view->help = true;
        $this->view->sidebarMenu = $this->arrMenu;
    }

    public function questionAction()
    {
        $this->setMenu();
        $this->view->help = true;
        $this->view->sidebarMenu = $this->arrMenu;
    }

    public function argumentsAction()
    {
        $this->setMenu();
        $this->view->help = true;
        $this->view->sidebarMenu = $this->arrMenu;
    }

    public function documentsAction()
    {
        $this->setMenu();
        $this->view->help = true;
        $this->view->sidebarMenu = $this->arrMenu;
    }

    public function penaltyAction()
    {
        $this->setMenu();
        $this->view->help = true;
        $this->view->sidebarMenu = $this->arrMenu;
    }

    public function terms44Action()
    {
        $this->setMenu();
        $this->view->help = true;
        $this->view->sidebarMenu = $this->arrMenu;
    }

    public function contrcasesAction()
    {
        $this->setMenu();
        $this->view->help = true;
        $this->view->sidebarMenu = $this->arrMenu;
    }

    public function jurisdictionAction()
    {
        $this->setMenu();
        $this->view->help = true;
        $this->view->sidebarMenu = $this->arrMenu;
    }
}