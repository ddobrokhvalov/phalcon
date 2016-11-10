<?php

namespace Multiple\Frontend\Controllers;
use Phalcon\Mvc\Controller;
use Multiple\Library\Exceptions\FieldException;
use Multiple\Library\Exceptions\MessageException;
use Multiple\Frontend\Validator\CallbackValidator;


class HelpController extends ControllerBase
{
    private $arrMenu = array(
        'FAQ по пользованию сервисом' => '/help/faq',
        'Часто задаваемые вопросы' => '/help/question',
        'Нормативные документы' => '/help/documents',
        'Сроки в 44-Ф3' => '/help/terms44',
        'Подведомственность' => '/help/jurisdiction',
        'Спорные случаи' => '/help/contrcases',
        'Штрафы' => '/help/penalty',
        'Типичные доводы жалоб' => '/help/arguments',
    );


    public function questionAction()
    {
        $this->setHelpMenu();
    }

    public function argumentsAction()
    {
        $this->setHelpMenu();
    }

    public function documentsAction()
    {
        $this->setHelpMenu();
    }

    public function penaltyAction()
    {
        $this->setHelpMenu();
    }

    public function terms44Action()
    {
        $this->setHelpMenu();
    }

    public function contrcasesAction()
    {
        $this->setHelpMenu();
    }

    public function jurisdictionAction()
    {
        $this->setHelpMenu();
    }

    public function aboutAction()
    {
        if ($this->user) {
            $this->setHelpMenu();
            $this->view->showHeader = false;
        } else {
            $this->view->host = $this->request->getHttpHost();
            $this->view->showHeader = true;
        }
    }

    public function contactAction()
    {
        if ($this->user) {
            $this->setHelpMenu();
            $this->view->conversion = $this->user->conversion;
            $this->view->phone = $this->user->phone;
            $this->view->email = $this->user->email;
            $this->view->showHeader = false;
        } else {
            $this->view->host = $this->request->getHttpHost();
            $this->view->showHeader = true;
        }
    }

    public function faqAction(){
        if ($this->user) {
            $this->setHelpMenu();
            $this->view->showHeader = false;
        } else {
            $this->view->host = $this->request->getHttpHost();
            $this->view->showHeader = true;
        }
    }

    public function sendMailFromContactAction(){
        try {
            $data = $this->request->getPost();
            $validation = new CallbackValidator();
            $messages = $validation->validate($data);
            if(count($messages)) throw new MessageException($messages);

            $data = $this->request->getPost();
            $message = $this->mailer->createMessageFromView('../views/emails/formContact', array(
                'host' => $this->request->getHttpHost(),
                'email' => $data['email'],
                'name' => $data['conversion'],
                'phone' => $data['phone'],
                'message' => $data['message']
            ))
                ->to($this->adminsEmails['callback'])
                ->subject('Обратный звонок в интеллектуальной системе ФАС-Онлайн');
            $message->send();
            $this->flashSession->success('Сообщение отправлено');
        } catch (MessageException $messages) {
            foreach ($messages->getArrErrors() as $message) {
                $this->flashSession->error($message->getMessage());
            }
        } catch (FieldException $e){
            $this->flashSession->error($e->getMessage());
        }
        $this->response->redirect('/help/contact');
    }

    private function setHelpMenu(){
        $this->setMenu();
        $this->view->help = true;
        $this->view->sidebarMenu = $this->arrMenu;
    }
}