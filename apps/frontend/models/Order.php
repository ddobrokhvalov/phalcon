<?php
/**
 * Created by PhpStorm.
 * User: knubisoft
 * Date: 17.10.2016
 * Time: 10:54
 */

namespace Multiple\Backend\Models;


class Order
{
    public $id;
    public $user_id;
    public $phone;
    public $email;
    public $firstname;
    public $lastname;
    public $patronymic;
    public $number;
    public $date;

    public function initialize()
    {
        $this->setSource('orders');
        $this->belongsTo(
            "user_id",
            "user",
            "id"
        );
    }

    public function getSource()
    {
        return 'orders';
    }
}