<?php
namespace Multiple\Backend\Models;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class ArgumentsCategory extends Model
{
    public $id;
    public $name;
    private $_categories;
    public $getCat = array();
    public $currentCat;
    public $required;

    public function initialize()
    {
        $this->setSource('arguments_categoty');
    }

    public function getSource()
    {
        return 'arguments_categoty';
    }

    public function getAllCategory(){
        $data = $this->find();
        $temp_arr = array();
        foreach($data as $value){
            $temp_arr[] = array(
                'id' => $value->id,
                'name' => $value->name,
                'parent_id' => $value->parent_id
            );
        }
        return $temp_arr;
    }

    function buildTreeArray($arItems, $section_id = 'parent_id', $element_id = 'id') {
        $childs = array();
        if(!is_array($arItems) || empty($arItems)) {
            return array();
        }
        foreach($arItems as &$item) {
            if(!$item[$section_id]) {
                $item[$section_id] = 0;
            }
            $childs[$item[$section_id]][] = &$item;
        }
        unset($item);
        foreach($arItems as &$item) {
            if (isset($childs[$item[$element_id]])) {
                $item['childs'] = $childs[$item[$element_id]];
            }
        }
        return $childs[0];
    }
}