<?php
namespace Multiple\Library;
use Multiple\Backend\Models\Log as LogModel;
class Log{
    public static function addLog($admin_id = false,$user_id = false, $target,$log_type){
          $log = new LogModel();
             $log->admin_id = $admin_id;
             $log->target = $target;
             $log->log_type = $log_type;
             $log->user_id = $user_id;
             $log->date = date("Y-m-d H:i:s");

             $log->save();

    }

}