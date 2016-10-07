<?php
namespace Multiple\Backend\Validator;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;

class RegisterValidator extends Validation
{


    public function initialize()
    {
        $this->add('email', new PresenceOf(array('message' => 'The e-mail is required')));
        $this->add('email', new Email(array('message' => 'The e-mail is not valid')));
        $this->add('password', new PresenceOf(array('message' => 'The password is required')));
        $this->add('confpassword', new PresenceOf(array('message' => 'The confpassword is required')));
        $this->add('phone', new PresenceOf(array('message' => 'The confpassword is required')));
    }
}