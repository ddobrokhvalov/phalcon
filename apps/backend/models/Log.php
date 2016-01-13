<?php
namespace Multiple\Backend\Models;

use Phalcon\Mvc\Model;

class Log extends Model
{
    public $id;
    public $au;
    public $type;
    public $text;
    public $customer_id;
    public $additionally;
    public $date;

    public function initialize()
    {
        $this->setSource("log");
    }

    public function getSource()
    {
        return "log";
    }

    public function getTypeList()
    {
        $this->db = $this->getDi()->getShared('db');
        $result = $this->db->query('SELECT type FROM `log` GROUP BY type');
        return $result->fetchAll();
    }

    public function filterLog($data)
    {
        if (!$data) {
            return Log::find();
        }
        $where = '';
        $bind = array();
        if (isset($data['au']) && $data['au'] != 'all') {
            $where = ' au = :au: ';
            $bind = array('au' => $data['au']);
        }
        if (isset($data['type']) && $data['type'] != 'all') {
            if ($where)
                $where .= ' AND type = :type: ';
            else
                $where = ' type = :type: ';
            $bind['type'] = $data['type'];
        }
        if (isset($data['additionally']) && $data['additionally'] != '') {
            if ($where)
                $where .= ' AND additionally = :additionally: ';
            else
                $where = ' additionally = :additionally: ';
            $bind['additionally'] = $data['additionally'];
        }
        if (isset($data['textsearch']) && $data['textsearch'] != '') {
            if ($where)
                $where .= ' AND text LIKE  :textsearch:  ';
            else
                $where = ' text  LIKE  :textsearch: ';
            $bind['textsearch'] = '%' . $data['textsearch'] . '%';
        }

        return Log::find(array(
            $where,
            'bind' => $bind
        ));
    }
}