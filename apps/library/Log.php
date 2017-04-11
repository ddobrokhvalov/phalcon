<?php

namespace Multiple\Library;

use Multiple\Backend\Models\Log as LogModel;

class Log
{
    public static $typeAdminAuth = 'Aвторизация админа';
    public static $textAdminLogin = 'Администратор вход в систему.';
    public static $textAdminLogout = 'Администратор выход из системы.';

    public static function addAdminLog($type, $text, $admin, $additionally = false, $user_type = '')
    {
        $log = new LogModel();
        $log->au = !$user_type ? 'администратор' : $user_type;
        $log->type = $type;
        $log->text = $text . ' ' . $admin->getSurnameAndInitials() . ' ' . $admin->getEmail();
        $log->customer_email = $admin->getEmail();
        if ($additionally)
            $log->additionally = $additionally;

        $log->date = date("Y-m-d H:i:s");
        $log->saveLog();
    }
}