<?php

namespace Multiple\Frontend\Validator;

use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Regex;

class EditUserValidator extends Validation
{
    public function initialize()
    {
        $this->add('conversion', new PresenceOf(array('message' => 'Как к вам обращаться обязательное поле','cancelOnFail' => true)));
        $this->add('mobile_phone', new PresenceOf(array('message' => 'Мобильный телефон обязательное поле','cancelOnFail' => true)));
//        $this->add('mobile_phone', new Regex(
//            [
//                "message"    => "Некорректный номер",
//                "pattern"    => "/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/",
//                "cancelOnFail" => true,
//            ]
//        ));
    }
}