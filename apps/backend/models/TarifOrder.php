<?php
namespace Multiple\Backend\Models;

use Phalcon\Mvc\Model;
use Phalcon\Db\RawValue;

class TarifOrder extends Model
{
	public $id;
	public $user_id;
	public $order_number;
	public $applicant_id;
	public $applicant_type;
	public $applicant_side;
	public $address;
	public $post_address;
	public $name_full;
	public $name_short;
	public $inn;
	public $kpp;
	public $phone;
	public $email;
	public $tarif_count;
	public $tarif_price_one;
	public $tarif_price;
	public $tarif_id;
	public $tarif_name;
	public $invoce_payment;
	public $order_date;
	
	
	public function initialize()
    {
        $this->setSource('tarif_order');
	}
	
	public function getSource()
    {
        return 'tarif_order';
    }
	
	
}