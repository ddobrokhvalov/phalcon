<?php
namespace Multiple\Backend\Validator;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;

class AdminValidator extends Validation
{
    
    
    public function initialize()
    {
        $this->add('email',new PresenceOf(array('message' => 'The e-mail is required')));
        $this->add('email',new Email(array('message' => 'The e-mail is not valid')));
        #$this->add('phone', new Regex(array('message' => 'The telephone is required','pattern' => '/\+44 [0-9]+/')));//todo: phone validator
        #$this->add('phone', new StringLength(array('messageMinimum' => 'The telephone is too short','min'=> 2)));
    }
}