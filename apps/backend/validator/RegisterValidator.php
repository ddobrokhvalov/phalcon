<?php
namespace Multiple\Backend\Validator;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Regex;

class RegisterValidator extends Validation
{


    public function initialize()
    {
        $this->add('email', new PresenceOf(array('message' => 'The e-mail is required',"cancelOnFail" => true,)));
        $this->add('email', new Email(array('message' => 'The e-mail is not valid')));
        $this->add('password', new PresenceOf(array('message' => 'The password is required', "cancelOnFail" => true,)));
        $this->add('password', new StringLength(array('min' => 8, 'message' => 'Password is short', "cancelOnFail" => true,)));
        $this->add('password', new Regex(
            [
                "message"    => "The wrong password",
                "pattern"    => "/^[\S]+$/",
                "cancelOnFail" => true,
            ]
        ));
        $this->add('confpassword', new PresenceOf(array('message' => 'The confpassword is required',"cancelOnFail" => true,)));
        $this->add('confpassword', new StringLength(array('min' => 8, 'message' => 'Confpassword is short',"cancelOnFail" => true,)));
        $this->add('confpassword', new Regex(
            [
                "message"    => "The wrong confpassword",
                "pattern"    => "/^[\S]+$/",
                "cancelOnFail" => true,
            ]
        ));
        $this->add('phone', new PresenceOf(array('message' => 'The confpassword is required',"cancelOnFail" => true,)));
        $this->add('phone', new Regex(
            [
                "message"    => "The wrong phone",
                "pattern"    => "/[+0-9]+/",
                "cancelOnFail" => true,
            ]
        ));
    }
}

