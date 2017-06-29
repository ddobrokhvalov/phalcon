<?php
namespace Multiple\backend\Models;

use Phalcon\Mvc\Model;

class UsersArguments extends Model
{
    public $id;
    public $argument_id;
    public $text;
    public $complaint_id;
    public $argument_order;
    public $argument_category_id;

    public function initialize()
    {
        $this->setSource('users_arguments');
    }

    public function getSource()
    {
        return 'users_arguments';
    }
	
	public function copyUsersArguments($old_complaint_id, $new_complaint_id){
		$db = $this->getDi()->getShared('db');
		$sql = "select * from users_arguments where complaint_id = '".$old_complaint_id."'";
		$result = $db->query($sql);
		$old_users_arguments = $result->fetchAll();
		foreach($old_users_arguments as $old_users_argument){
			$new_users_argument_sql = "insert into users_arguments 
										(argument_id, 
										text, 
										complaint_id, 
										argument_order, 
										argument_category_id)
										values 
										('".$old_users_argument['argument_id']."', 
										'".$old_users_argument['text']."', 
										'".$new_complaint_id."', 
										'".$old_users_argument['argument_order']."', 
										'".$old_users_argument['argument_category_id']."')";
			$db->query($new_users_argument_sql);
		}
	}

}