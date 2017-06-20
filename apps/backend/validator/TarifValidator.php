<?php
namespace Multiple\Backend\Validator;
use Phalcon\Validation;
use Phalcon\Validation\Validator\Email;
use Phalcon\Validation\Validator\PresenceOf;
//use Phalcon\Validation\Validator\Regex;
//use Phalcon\Validation\Validator\StringLength;

class TarifValidator extends Validation
{
    public function initialize()
    {
        $this->add('tarif_name',new PresenceOf(array('message' => 'Заполните название тарифа')));
        $this->add('tarif_type',new PresenceOf(array('message' => 'Выберите тип тарифа')));
        $this->add('tarif_price',new PresenceOf(array('message' => 'Заполните стоимость единицы')));
        $this->add('tarif_discount',new PresenceOf(array('message' => 'Заполните уменьшение стоимости')));
    }
}