<?php
namespace Multiple\Frontend\Validator;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Regex;

class RegisterValidator extends Validation
{


    public function initialize()
    {
//        $this->add('name', new PresenceOf(array('message' => 'Имя обязательное поле','cancelOnFail' => true)));
//        /*$this->add('name', new Regex(
//            [
//                "message"    => "Некорректное имя",
//                "pattern"    => "/^[\w]+$/",
//                "cancelOnFail" => true,
//            ]
//        ));*/
        $this->add('email', new PresenceOf(array('message' => 'E-mail обязательное поле','cancelOnFail' => true)));
        $this->add('email', new Email(array('message' => 'Некорректный E-mail','cancelOnFail' => true)));
        $this->add('password', new PresenceOf(array('message' => 'Пароль обязательное поле', 'cancelOnFail' => true)));
        $this->add('password', new StringLength(array('min' => 8, 'messageMinimum' => 'Пароль менее 8 символов', 'cancelOnFail' => true)));
        $this->add('password', new Regex(
            [
                "message"    => "Некорректный пароль",
                "pattern"    => "/^[\S]+$/",
                "cancelOnFail" => true,
            ]
        ));
//        $this->add('confpassword', new PresenceOf(array('message' => 'Проверка пароля обязательное поле','cancelOnFail' => true)));
//        $this->add('confpassword', new StringLength(array('min' => 8, 'messageMinimum' => 'Проверка пароля менее 8 символов','cancelOnFail' => true)));
//        $this->add('confpassword', new Regex(
//            [
//                "message"    => "Некорректный пароль",
//                "pattern"    => "/^[\S]+$/",
//                "cancelOnFail" => true,
//            ]
//        ));
//        $this->add('phone', new PresenceOf(array('message' => 'Телефон обязательное поле','cancelOnFail' => true)));
//        $this->add('phone', new Regex(
//            [
//                "message"    => "Некорректный телефон",
//                "pattern"    => "/[+0-9]+/",
//                "cancelOnFail" => true,
//            ]
//        ));
    }
}

