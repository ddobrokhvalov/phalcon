<?php
/**
 * Created by PhpStorm.
 * User: knubisoft
 * Date: 14.10.2016
 * Time: 15:38
 */

namespace Multiple\Frontend\Validator;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;

class LoginValidator extends Validation
{
    public function initialize()
    {
        $this->add('email', new PresenceOf(array('message' => 'E-mail обязательное поле', 'cancelOnFail' => true)));
        $this->add('email', new Email(array('message' => 'Некорректный E-mail', 'cancelOnFail' => true)));
        $this->add('password', new PresenceOf(array('message' => 'Пароль обязательное поле', 'cancelOnFail' => true)));
    }
}