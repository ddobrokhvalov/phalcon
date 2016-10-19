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
        $this->add('firstname', new PresenceOf(array('message' => 'Имя обязательное поле','cancelOnFail' => true)));
        $this->add('lastname', new PresenceOf(array('message' => 'Фамилия обязательное поле','cancelOnFail' => true)));
        $this->add('patronymic', new PresenceOf(array('message' => 'Отчество обязательное поле','cancelOnFail' => true)));
        $this->add('phone', new PresenceOf(array('message' => 'Телефон обязательное поле','cancelOnFail' => true)));
        $this->add('phone', new Regex(
            [
                "message"    => "Некорректный номер",
                "pattern"    => "/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/",
                "cancelOnFail" => true,
            ]
        ));
    }
}