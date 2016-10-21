<?php
/**
 * Created by PhpStorm.
 * User: knubisoft
 * Date: 21.10.2016
 * Time: 10:36
 */

namespace Multiple\Library\Exceptions;


class RegisterException extends \Exception
{
    private $field_name;

    public function __construct($message, $field_name = 'filed') {
        $this->field_name = $field_name;
        parent::__construct($message, 0, null);
    }

    public function getField(){
        return $this->field_name;
    }
}