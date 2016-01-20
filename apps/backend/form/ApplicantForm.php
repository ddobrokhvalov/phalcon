<?php
namespace Multiple\Backend\Form;

use Phalcon\Forms\Form;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Select;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Numericality;
use Phalcon\Validation\Validator\StringLength;

class ApplicantForm extends Form
{

    public function initialize($entity = null, $options = array())
    {
        if (!isset($options['edit']) && !isset($options['add'])){
            $element = new Text("id");
            $this->add($element->setLabel("Id"));

        } else {
            $this->add(new Hidden("id"));
        }
        //
        $this->add(
            new Select(
                "type",
                array(
                    'юридическое лицо' => 'Юридическое лицо',
                    'индивидуальный предприниматель' => 'Индивидуальный предприниматель',
                    'физическое лицо' => 'Физическое лицо'
                )
            )
        );
        //
        $name_full = new Text('name_full');
        $name_full->setLabel('Полное наименование');
        $name_full->setFilters(array('striptags', 'string'));
        $name_full->addValidators(
            array(
                new PresenceOf(
                    array(
                        'message' => 'Полное наименование обязательно'
                    )
                )
            )
        );
        $this->add($name_full);
        //
        $name_short = new Text('name_short');
        $name_short->setLabel('Короткое наименование');
        $name_short->setFilters(array('striptags', 'string'));
        $name_short->addValidators(
            array(
                new PresenceOf(
                    array(
                        'message' => 'Короткое наименование обязательно'
                    )
                )
            )
        );
        $this->add($name_short);
        //
        $inn = new Text('inn');
        $inn->setLabel('Инн');
        $inn->setFilters(array('striptags', 'string'));
        $inn->addValidators(
            array(
                new PresenceOf(
                    array(
                        'message' => 'Инн обязательно'
                    )
                )
            )
        );
        $this->add($inn);
        //
        $kpp = new Text('kpp');
        $kpp->setLabel('КПП');
        $kpp->setFilters(array('striptags', 'string'));
        $kpp->addValidators(
            array(
                new PresenceOf(
                    array(
                        'message' => 'КПП обязательно'
                    )
                )
            )
        );
        $this->add($kpp);
        //
        $address = new Text('address');
        $address->setLabel('Адрес местонахождения');
        $address->setFilters(array('striptags', 'string'));
        $address->addValidators(
            array(
                new PresenceOf(
                    array(
                        'message' => 'Адрес местонахождения обязательно'
                    )
                )
            )
        );
        $this->add($address);
        //
        $position = new Text('position');
        $position->setLabel('Должность заявителя');
        $position->setFilters(array('striptags', 'string'));
        $position->addValidators(
            array(
                new PresenceOf(
                    array(
                        'message' => 'Должность заявителя обязательно'
                    )
                )
            )
        );
        $this->add($position);
        //
        $fio_applicant = new Text('fio_applicant');
        $fio_applicant->setLabel('ФИО заявителя');
        $fio_applicant->setFilters(array('striptags', 'string'));
        $fio_applicant->addValidators(
            array(
                new PresenceOf(
                    array(
                        'message' => 'ФИО заявителя заявителя обязательно'
                    )
                )
            )
        );
        $this->add($fio_applicant);
        //
        $fio_contact_person = new Text('fio_contact_person');
        $fio_contact_person->setLabel('ФИО контактного лица');
        $fio_contact_person->setFilters(array('striptags', 'string'));
        $fio_contact_person->addValidators(
            array(
                new PresenceOf(
                    array(
                        'message' => 'ФИО контактного лица обязательно'
                    )
                )
            )
        );
        $this->add($fio_contact_person);
        //
        $telefone = new Text('telefone');
        $telefone->setLabel('Контактный факс, телефон');
        $telefone->setFilters(array('striptags', 'string'));
        $telefone->addValidators(
            array(
                new PresenceOf(
                    array(
                        'message' => 'Контактный факс, телефон обязательно'
                    )
                )
            )
        );
        $this->add($telefone);
       ///
        $telefone = new Text('email');
        $telefone->setLabel('Контактный факс, телефон');
        $telefone->setFilters(array('striptags', 'string'));
        $telefone->addValidators(
            array(
                new PresenceOf(
                    array(
                        'message' => 'Контактный факс, телефон обязательно'
                    )
                )
            )
        );
        $this->add($telefone);
        ///
        $email = new Text('email');
        $email->setLabel('email');
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
    }
}