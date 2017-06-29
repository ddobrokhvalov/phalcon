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
	
	public function getCategoryNotEmpty($type, $req, $checkRequired){
        $sql = "SELECT t1.name as lvl1, t1.id as lvl1_id, t1.required as lvl1_required , t2.name as lvl2, t2.id as lvl2_id, t2.required as lvl2_required , t3.name as lvl3, t3.id as lvl3_id, `arg1`.*
FROM `arguments_categoty` as t1
JOIN `arguments_categoty` as t2 ON t2.parent_id = t1.id
LEFT JOIN `arguments_categoty` as t3 on t3.parent_id = t2.id
RIGHT JOIN `arguments` as `arg1` ON (t2.id = `arg1`.`category_id` AND `arg1`.`type` LIKE '%{$type}%') OR (t3.id = `arg1`.`category_id` AND `arg1`.`type` LIKE '%{$type}%')
WHERE t1.parent_id = 0 ";
        if($req == 1 && $checkRequired == 0) $sql .= ' AND (t1.required = 1 OR t2.required = 1 OR t3.required = 1)';
        if($req == 0) $sql .= '  AND (t1.required = 0 AND t2.required = 0 OR t3.required = 0)';
        $arg = new ArgumentsCategory();
        return new Resultset(null, $arg, $arg->getReadConnection()->query($sql));
    }
	
}