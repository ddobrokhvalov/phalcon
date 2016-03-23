<?php
namespace Multiple\Library;
class PaginatorBuilder{

    static function buildPaginationArray($current_page = 1, $page_count = 1, $page_strike_length = 2){
        $paginator_array = [];
        for($index = 1; $index<=$page_count; $index++){
            if($index<=$page_strike_length || $index>($page_count-$page_strike_length)
                || ($index>($current_page - $page_strike_length) && $index<($current_page + $page_strike_length))) {
                if ($index == $current_page)
                    $paginator_array[] = array('type' => 'current', 'num' => $index);
                else
                    $paginator_array[] = array('type' => 'page', 'num' => $index);
            }
            elseif($index<$current_page - $page_strike_length){
                $paginator_array[] = array('type'=>'delimer');
                $index= $current_page - $page_strike_length;
            } else {
                $paginator_array[] = array('type'=>'delimer');
                $index = $page_count - $page_strike_length;
            }

        }
        return $paginator_array;
    }

}