<?php
namespace Multiple\Backend\Form;
use Phalcon\Forms\Form;
use Phalcon\Forms\Element\File;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\StringLength;

class AdminForm extends Form
{
    /**
     * Инициализация формы
     */
    public function initialize($entity = null, $options = array())
    {
        if (!isset($options['edit']) && !isset($options['add'])) {
            $element = new Text("id");
            $this->add($element->setLabel("Id"));

        } else {
            $this->add(new Hidden("id"));
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
        //
        $surname = new Text("surname");
        $surname->setLabel("Фамилия");
        $surname->setFilters(array('striptags', 'string'));
        $surname->addValidators(
            array(
                new PresenceOf(
                    array(
                        'message' => 'Фамилия обязательно'
                    )
                )
            )
        );
        $this->add($surname);
        //
        $name = new Text("name");
        $name->setLabel("Имя");
        $name->setFilters(array('striptags', 'string'));
        $name->addValidators(
            array(
                new PresenceOf(
                    array(
                        'message' => 'Имя обязательно'
                    )
                )
            )
        );
        $this->add($name);
        //
        $patronymic = new Text("patronymic");
        $patronymic->setLabel("Отчество");
        $patronymic->setFilters(array('striptags', 'string'));
        $patronymic->addValidators(
            array(
                new PresenceOf(
                    array(
                        'message' => 'Отчество обязательно'
                    )
                )
            )
        );
        $this->add($patronymic);
        if (isset($options['add'])) {

            $password = new Text('password');
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
        if (isset($options['edit'])) {
            $emptypassword= new Text('emptypassword');
            $emptypassword->setLabel("Пароль");
            $this->add($emptypassword);
        }
       //
        if (isset($options['edit'])) {
            $avatar = new File("avatar");
            $avatar->setLabel("Avatar");
            $this->add($avatar);
        }


    }
}