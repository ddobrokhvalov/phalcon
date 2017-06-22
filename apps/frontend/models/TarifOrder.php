<?php
/**
 * Created by 
 * User: ddobrokhvalov
 * Date: 15.06.2017
 */

namespace Multiple\Frontend\Models;
use Phalcon\Mvc\Model;

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
	
	 public function saveTarifOrder($data){
        
		$db = $this->getDi()->getShared('db');
		$sql = "select max(order_number) as last_order_num from tarif_order 
				where user_id = '".$data['user_id']."'";
		$result = $db->query($sql);
		$last_record = $result->fetch();
		if($last_record && $last_record["last_order_num"]){
			$this->order_number = $last_record["last_order_num"]+1;
		}
		
		$this->user_id = $data['user_id'];
        $this->applicant_id = $data['applicant_id'];
		$this->applicant_type = $data['applicant_type'];
		$this->applicant_side = $data['applicant_side'];
		$this->address = $data['address'];
		$this->post_address = $data['post_address']?$data['post_address']:$data['address'];
		$this->name_full = $data['name_full'];
		$this->name_short = $data['name_short'];
		$this->inn = $data['inn'];
		$this->kpp = $data['kpp'];
		$this->phone = $data['phone'];
		$this->email = $data['email'];
		$this->tarif_count = $data['tarif_count'];
		$this->tarif_price_one = $data['tarif_price_one'];
		$this->tarif_price = $data['tarif_price'];
		$this->tarif_id = $data['tarif_id'];
		$this->tarif_name = $data['tarif_name'];
		
        return $this->save();
    }
	
	public function getTarifOrders($user_id, $tarif_id, $tarif_count){
		$db = $this->getDi()->getShared('db');
		$sql = "select * from tarif_order
				where user_id = $user_id and tarif_id = $tarif_id and tarif_count like '".$tarif_count."%'
				order by order_date desc";
		$result = $db->query($sql);
		$last_records = $result->fetchAll();
		return $last_records;
	}
	
}