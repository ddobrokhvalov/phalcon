<?php
namespace Multiple\Library;
use Phalcon\DI;
class TestLib{

    public function alc(){
        $di = DI::getDefault();
        var_dump($di->get('router')->getMatchedRoute()->getName());
    }
    public function f(){
        $di = DI::getDefault();
        $connection = $di->get('db');

        var_dump($connection);



    }
}