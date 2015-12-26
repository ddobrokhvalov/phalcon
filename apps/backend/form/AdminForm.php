<?php
namespace Multiple\Form;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;

class AdminForm extends Form
{
    /**
     * Инициализация формы
     */
    public function initialize($entity = null, $options = array())
    {
        if (!isset($options['edit'])) {
            $element = new Text("id");
            $this->add($element->setLabel("Id"));

        } else {
            //$this->add(new Hidden("id"));
        }

        $email = new Text("email");
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
        if (isset($options['edit'])) {
            $password = new Text('Password');
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
        $role = new Select(
            'role',
           array('admin'=>'Admin','manager'=>'Manager'),
            array(
                'using' => array('id', 'name'),
                'useEmpty' => true,
                'emptyText' => '...',
                'emptyValue' => ''
            )
        );
        $this->add($role);


    }
}