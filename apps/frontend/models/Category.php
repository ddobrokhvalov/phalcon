<?php
namespace Multiple\Frontend\Models;
use Multiple\Frontend\Models\Template;
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
        $categorys = Category :: find();
        $result = array();

        foreach ($categorys as $category){
           // var_dump($category); exit;
            $templates = Template::find(
                array(
                    "category_id = :category_id: ",
                    'bind' => array(
                        'category_id' => $category->id
                    )
                )
            );
           $result[] = array(
               'category' => $category,
               'templates' =>$templates
           );

        }
      return  $result;
    }
}