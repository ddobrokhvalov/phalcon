<?php
namespace Multiple\Library;
class PaginatorBuilder{

    public
        $_current_page,
        $_page_strike_length,
        $_page_count;

    function __construct($current_page = 1, $page_count = 1, $page_strike_length = 2){
        $this->_current_page =  $current_page;
        $this->_page_count =  $page_count;
        $this->_page_strike_length =  $page_strike_length;
    }

    function buildPaginationArray(){
        $paginator_array = [];
        for($index = 1; $index<=$this->_page_count; $index++){
            if($index<=$this->_page_strike_length || $index>($this->_page_count-$this->_page_strike_length)
                || ($index>($this->_current_page - $this->_page_strike_length) && $index<($this->_current_page + $this->_page_strike_length))) {
                if ($index == $this->_current_page)
                    $paginator_array[] = array('type' => 'current', 'num' => $index);
                else
                    $paginator_array[] = array('type' => 'page', 'num' => $index);
            }
            elseif($index<$this->_current_page - $this->_page_strike_length){
                $paginator_array[] = array('type'=>'delimer');
                $index= $this->_current_page - $this->_page_strike_length;
            } else {
                $paginator_array[] = array('type'=>'delimer');
                $index = $this->_page_count - $this->_page_strike_length;
            }

        }
        return $paginator_array;
    }

}