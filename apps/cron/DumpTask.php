<?php

//use Multiple\Library\Mailer;
use Multiple\Library\Mysqldump;
use Phalcon\Di;

#https://github.com/ifsnop/mysqldump-php
class DumpTask extends \Phalcon\Cli\Task
{
    public
        $_dumpdir;

    public function mainAction()
    {
        $this->_dumpdir = realpath(APP_PATH . '/../../mysqldump/');
        //php /var/www/bca/bca/public/cli.php dump
        try {
            $this->arhiveLastTree();
            $this->makeBU();
//            $mail = Mailer::getCronMail();
//            $mail->Subject = "Cron 2 Success " . date('Y-m-d');
//            $mail->Body = "My Dear Friend, THE Cron BackUp Success " . date('Y-m-d');
//            $mail->Send();
            exit;

        } catch (Exception $ex) {

//            $mail = Mailer::getCronMail();
//            $mail->Subject = "THE CRON BACKUP ERROR! ALARM! WE HAVE NO BACKUP! WE ARE IN DANGER!" . date('Y-m-d');
//            $mail->Body = "Cron BackUp Error:" . $ex->getMessage();
//            $mail->Send();

        }
    }

    public function makeBU()
    {
        $di = Di::getDefault();
        $db = $di->get("dbconfig");
        $dump = new Mysqldump('mysql:host=' . $db->host . ';dbname=' . $db->dbname, $db->username, $db->password);
        $dump->start($this->_dumpdir . DIRECTORY_SEPARATOR . 'dump_' . date('Ymd') . '.sql');
        $zip = new ZipArchive();
        if ($zip->open($this->_dumpdir . DIRECTORY_SEPARATOR . 'dump_' . date('Ymd') . '.sql.zip', ZipArchive::CREATE) === true) {
            if ($zip->addFile($this->_dumpdir . DIRECTORY_SEPARATOR . 'dump_' . date('Ymd') . '.sql', 'dump_' . date('Ymd') . '.sql') !== true)
                throw new Exception('File Created, but not saved to arhive');
            $zip->close();
        } else {
            throw new Exception('Can\'t create the archive');
        }
    }

    public function arhiveLastTree()
    {
        $dumpfiles = scandir($this->_dumpdir);
        $dumpfiles = array_diff($dumpfiles, array('..', '.'));
        $weekday = strtotime("-7 day");
        $month = strtotime("-7 month"); //todo:
        foreach ($dumpfiles as $file)
            if (!in_array($file, array(".", "..")))
                if (!is_dir($this->_dumpdir . DIRECTORY_SEPARATOR . $file)) {
                    $info = pathinfo($this->_dumpdir . DIRECTORY_SEPARATOR . $file);
                    if ($info['extension'] == 'sql') {
                        if ($info['basename'] !== 'dump_' . date('Ymd') . '.sql')
                            @unlink($this->_dumpdir . DIRECTORY_SEPARATOR . $file);
                    } else if ($info['extension'] == 'zip') {
                        if (date('d', $weekday) != 1 && $info['basename'] === 'dump_' . date('Ymd', $weekday) . '.sql.zip')
                            @unlink($this->_dumpdir . DIRECTORY_SEPARATOR . $file);
                        else if ($info['basename'] === 'dump_' . date('Ym', $month) . '01.sql.zip')
                            @unlink($this->_dumpdir . DIRECTORY_SEPARATOR . $file);
                    }
                }

    }
}