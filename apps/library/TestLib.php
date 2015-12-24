<?php
namespace Multiple\Library;
use Phalcon\DI;
class TestLib{

    public function f(){
        $di = DI::getDefault();
        $connection = $di->get('db');

        var_dump($connection);
        var_dump('lib function done');


    }
}