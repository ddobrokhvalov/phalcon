<?php
namespace Multiple\Backend\Models;

use Phalcon\Mvc\Model;
use Phalcon\Db\RawValue;

class Tarif extends Model
{
	public $id;
	public $tarif_name;
	public $tarif_anounce;
	public $tarif_description;
	public $tarif_type;
	public $tarif_price;
	public $tarif_discount;
	
	public function initialize()
    {
        $this->setSource('tarifs');
	}
	
	public function getSource()
    {
        return 'tarifs';
    }
	
	public function getTarifs($priced = false){
		$db = $this->getDi()->getShared('db');
		$sql = "select * from tarifs";
		if($priced){
			$sql .= " where tarif_price > 0";
		}
		$result = $db->query($sql);
         return $result->fetchAll();
	}
	
}