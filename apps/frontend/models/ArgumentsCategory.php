<?php
namespace Multiple\Frontend\Models;

use Phalcon\Mvc\Model;
use Phalcon\Db;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;

class ArgumentsCategory extends Model
{
    public $id;
    public $name;

    public function initialize()
    {
        $this->setSource('arguments_categoty');
    }

    public function getSource()
    {
        return 'arguments_categoty';
    }




    public function getCategoryNotEmpty($type){
        $query = $this->getModelsManager();

        $sql = "SELECT t1.name as lvl1, t1.id as lvl1_id, t1.required as lvl1_required , t2.name as lvl2, t2.id as lvl2_id, t2.required as lvl2_required , t3.name as lvl3, t3.id as lvl3_id, `arg1`.*
FROM `arguments_categoty` as t1
JOIN `arguments_categoty` as t2 ON t2.parent_id = t1.id
LEFT JOIN `arguments_categoty` as t3 on t3.parent_id = t2.id
RIGHT JOIN `arguments` as `arg1` ON (t2.id = `arg1`.`category_id` AND `arg1`.`type` LIKE '%{$type}%') OR (t3.id = `arg1`.`category_id` AND `arg1`.`type` LIKE '%{$type}%')
WHERE t1.parent_id = 0";
        $arg = new ArgumentsCategory();
        return new Resultset(null, $arg, $arg->getReadConnection()->query($sql));
    }
}