<?php
namespace Multiple\Frontend\Validator;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Regex;

class CallbackValidator extends Validation
{


    public function initialize()
    {
        $this->add('email', new PresenceOf(array('message' => 'E-mail обязательное поле','cancelOnFail' => true)));
        $this->add('email', new Email(array('message' => 'Некорректный E-mail','cancelOnFail' => true)));
        $this->add('conversion', new PresenceOf(array('message' => 'Как вас зовут обязательное поле','cancelOnFail' => true)));
        $this->add('phone', new PresenceOf(array('message' => 'Телефон обязательное поле','cancelOnFail' => true)));
        $this->add('message', new PresenceOf(array('message' => 'Сообщение обязательное поле','cancelOnFail' => true)));
        $this->add('phone', new Regex(
            [
                "message"    => "Некорректный номер",
                "pattern"    => "/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/",
                "cancelOnFail" => true,
            ]
        ));
    }
}

