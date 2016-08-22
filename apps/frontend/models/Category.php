<?php
namespace Multiple\Frontend\Models;
use Multiple\Frontend\Models\Template;
use Phalcon\Mvc\Model;

class Category extends Model
{
    public $id;
    public $name;
    public $cnt;

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
            $temp_arr = array();
            foreach($templates as $key => $val){
                $temp = strip_tags($val->text);
                $val->text = $temp;
                $temp_arr[] = $val;
            }
           $category->cnt = $templates->count();
           $result[] = array(
               'category' => $category,
               'templates' =>$temp_arr
           );
        }
      return  $result;
    }
}