<?php
namespace Multiple\Backend\Models;

use Phalcon\Mvc\Model;

class Category extends Model
{
    public $id;
    public $name;

    public function initialize()
    {
        $this->setSource("category_template");
    }

    public function getSource()
    {
        return "category_template";
    }

    public function getArguments(){
        $categorys = ArgumentsCategory::find();
        $result = array();

        foreach ($categorys as $category){
            $templates = Arguments::find(
                array(
                    "category_id = :category_id: ",
                    'bind' => array(
                        'category_id' => $category->id
                    )
                )
            );
            $category->cnt = $templates->count();
            $result[] = array(
                'category' => $category,
                'templates' =>$templates
            );
        }
        return  $result;
    }
}