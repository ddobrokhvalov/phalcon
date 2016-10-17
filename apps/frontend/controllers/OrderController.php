<?php
/**
 * Created by PhpStorm.
 * User: knubisoft
 * Date: 17.10.2016
 * Time: 9:58
 */

namespace Multiple\Frontend\Controllers;


use Multiple\Frontend\Models\User;
use Multiple\Backend\Models\Order;
use Multiple\Frontend\Validator\OrderValidator;

class OrderController extends ControllerBase
{
    public function orderAction(){
        try{
            //if($this->request->isPost()){
                $data = $this->request->getPost('auction_id');

                $user = User::findFirst(array(
                    "id = {$data['user_id']}"
                ));
                if(!$user) throw new \Exception('Такого пользователя нет');
           //}
            exit;
        } catch (\Exception $e){
            json_encode(array('error' => $e->getMessage()));

        }
    }
}