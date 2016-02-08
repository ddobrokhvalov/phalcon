<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;

class Template extends Model
{
    public $id;
    public $category_id;
    public $name;
    public $text;

    public function initialize()
    {
        $this->setSource("template");
    }

    public function getSource()
    {
        return "template";
    }
    public function findByCategoryId($cat_id){
        $result = Template::find(
            array(
                "category_id = :cat_id: ",
                'bind' => array(
                    'cat_id' => $cat_id
                )
            )
        );
        return $result;
    }

}