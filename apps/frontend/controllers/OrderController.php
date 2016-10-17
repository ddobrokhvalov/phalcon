<?php
/**
 * Created by PhpStorm.
 * User: knubisoft
 * Date: 17.10.2016
 * Time: 9:58
 */

namespace Multiple\Frontend\Controllers;


use Multiple\Frontend\Models\User;
use Multiple\Frontend\Models\Order;
use Multiple\Frontend\Validator\OrderValidator;
use Phalcon\Config\Adapter\Ini as ConfigIni;

class OrderController extends ControllerBase
{
    public function orderAction(){
        try{
            if($this->request->isPost()){
                $data = $this->request->getPost();
                $validation = new OrderValidator();
                $messages = $validation->validate($data);
                if(count($messages))  throw new \Exception('error');

                $user_id = $this->user->id;
                $user = User::findFirst(array(
                    "id = {$user_id}"
                ));
                if(!$user) throw new \Exception('Такого пользователя нет');

                $order = new Order();
                $order->phone = $user->phone;
                $order->user_id = $user->id;
                $order->firstname = $user->firstname;
                $order->lastname = $user->lastname;
                $order->patronymic = $user->patronymic;
                $order->email = $user->email;
                $order->auction_id = trim($data['auction_id']);
                $order->date = date('Y-m-d H:i:s');
                if ($order->save() === false) throw new \Exception('Ошибка создание заказа');

                $message = $this->mailer->createMessageFromView('../views/emails/order', array(
                    'hashreg'   => $user->hashreg,
                    'host'      => $this->request->getHttpHost(),
                    'order' => $order
                ))
                    ->to($this->adminsEmails['order'])
                    ->subject('Новый заказ в системе ФАС');
                $message->send();

                echo json_encode(array('status' => 'ok'));
                exit;
           }
        } catch (\Exception $e){
            $temp_err = array();
            if(isset($messages) && count($messages)) {
                foreach ($messages as $message) {
                    $temp_err[$message->getField()][] = $message->getMessage();
                }
            } else {
                $temp_err[] = $e->getMessage();
            }
            echo json_encode(array("error" => $temp_err));
            exit;
        }
    }
}