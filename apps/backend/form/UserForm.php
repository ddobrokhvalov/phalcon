<?php
namespace Multiple\Backend\Form;

use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\StringLength;
use Phalcon\Validation\Validator\Confirmation;


class UserForm extends Form
{

    public function initialize($entity = null, $options = array())
    {


        $this->setValidation(array(
            'notNullValidations' => false,
            'notEmptyValidations' => false,
            'allowEmpty' => true ));
        if (!isset($options['edit']) && !isset($options['add'])){
            $element = new Text("id");
            $this->add($element->setLabel("Id"));
        } else
            $this->add(new Hidden("id"));

        $email = new Text("email", array('id'   => 'userEmail'));
        $email->setLabel("Email");
        $email->setFilters(array('striptags', 'string'));
        $email->addValidators(
            array(
                new PresenceOf(
                    array(
                        'message' => 'Email обязательно'
                    )
                )
            )
        );
        $this->add($email);
        $this->add(new TextArea('admin_comment', array('id'=>'userComment')));
        $f = new Text('lastname', array('id'   => 'fUser'));
        $this->add(new Text('firstname', array('id'   => 'iUser')));
        $this->add($f);
        $this->add(new Text('patronymic', array('id'   => 'oUser')));
        $this->add(new Numeric('phone', array('id'   => 'telFaxUser')));
        if (isset($options['add'])) {
            $password = new Password('password');
            $password->setLabel("Пароль");
            $password->setFilters(array('striptags', 'string'));
            $password->addValidators(
                array(
                    new PresenceOf(
                        array(
                            'message' => 'пароль обязательно'
                        )
                    )
                )
            );
            $this->add($password);
        }
        $this->add(
            new Select(
                "status",
                array(
                    '1' => 'Активный',
                    '0' => 'Заблокированый'
                )
            )
        );
    }
}