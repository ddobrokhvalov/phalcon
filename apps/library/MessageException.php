<?php
/**
 * Created by PhpStorm.
 * User: knubisoft
 * Date: 20.10.2016
 * Time: 13:57
 */

namespace Multiple\Library;


class MessageException extends \Exception
{
    private $arrMessage;

    public function __construct($message, $code = 0, Exception $previous = null) {
        if(!empty($message)){
            $this->arrMessage = $message;
        } else {
            $this->arrMessage = array();
        }
        parent::__construct('message error watch value arrMessage', $code, $previous);
    }

    public function getArrErrors(){
        return $this->arrMessage;
    }
}