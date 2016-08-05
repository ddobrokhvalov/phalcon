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




    public function getCategoryNotEmpty($parent_id = 0){
        $query = $this->getModelsManager();

        $sql = "SELECT t1.name as lvl1, t2.name as lvl2, t3.name as lvl3,  null as lvl4 , t1.id as id_cat, t1.parent_id, `arg1`.*
FROM `arguments_categoty` as t1
JOIN `arguments_categoty` as t2 ON t2.parent_id = t1.id
JOIN `arguments_categoty` as t3 on t3.parent_id = t2.id
RIGHT JOIN `arguments` as `arg1` ON t3.id = `arg1`.`category_id`
WHERE t1.parent_id = $parent_id
GROUP BY t1.name

UNION

SELECT t1.name as lvl1,  t2.name as lvl2, t3.name as lvl3, t4.name as lvl4, t1.id as id_cat, t1.parent_id, `arg1`.*
FROM `arguments_categoty` as t1
JOIN `arguments_categoty` as t2 ON t2.parent_id = t1.id
JOIN `arguments_categoty` as t3 on t3.parent_id = t2.id
JOIN `arguments_categoty` as t4 on t4.parent_id = t3.id
JOIN `arguments` as `arg1` ON t4.id = `arg1`.category_id
WHERE t1.parent_id = $parent_id
GROUP BY t1.name";


        $arg = new ArgumentsCategory();

        return new Resultset(null, $arg, $arg->getReadConnection()->query($sql));
    }
}