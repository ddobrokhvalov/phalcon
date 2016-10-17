<?php
namespace Multiple\Frontend\Validator;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Regex;

class OrderValidator extends Validation
{


    public function initialize()
    {
        $this->add('auction_id', new PresenceOf(array('message' => 'Номер извещение обязательное поле','cancelOnFail' => true)));
        $this->add('auction_id', new StringLength(array('min' => 19, 'max' => 19, 'messageMinimum' => 'Номер извещение должен быть не менее 19 чисел','messageMaximum' => 'Номер извещение должен быть не более 19 чисел', 'cancelOnFail' => true)));
        $this->add('auction_id', new Regex(
            [
                "message"    => "Некорректный номер извещения",
                "pattern"    => "/^[0][\d]{18}$/",
                "cancelOnFail" => true,
            ]
        ));
    }
}

