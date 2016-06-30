<?php
namespace Multiple\Frontend\Controllers;
use Phalcon\Mvc\Controller;
use Multiple\Library\PHPMailer\PHPMailerAutoload;

class HelpController extends Controller
{
    public function faqAction()
    {//DebugBreak();
       // $this->setMenu();
       require_once __DIR__ . '/../../library/PHPMailer/PHPMailerAutoload.php';
       $mail = new \PHPMailer();

        //$mail->SMTPDebug = 3;                               // Enable verbose debug output

        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.yandex.ru';                       // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'forma@fas-online.ru';                 // SMTP username
        $mail->Password = 'passforma';                           // SMTP password
        $mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 465;                                    // TCP port to connect to

        $mail->setFrom('admin@fas-online.ru', 'Fas-online');
        $mail->addAddress('olezhkafp@gmail.com');     // Add a recipient
        //$mail->addAddress('ellen@example.com');               // Name is optional
        //$mail->addReplyTo('info@example.com', 'Information');
        //$mail->addCC('cc@example.com');
        //$mail->addBCC('bcc@example.com');

        //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
        $mail->isHTML(true);                                  // Set email format to HTML

        $mail->Subject = 'Форма fas-online контакты';
        $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        if(!$mail->send()) {
            $stat = 'Message could not be sent.';
            $stat .= 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            $stat = 'Message has been sent';
        }
        $this->view->messs = $stat;
       //require_once "Mail.php";

       /*$from = '<admin@fas-online.ru>'; //change this to your email address
       $to = '<olezhkafp@gmail.com>'; // change to address
       $subject = 'Форма fas-online контакты'; // subject of mail
       $body = "Hello world! this is the content of the email"; //content of mail

       $headers = array(
           'From' => $from,
           'To' => $to,
           'Subject' => $subject
       );

       $smtp = Mail::factory('smtp', array(
           'host' => 'ssl://smtp.yandex.ru',
           'port' => '465',
           'auth' => true,
           'username' => 'forma@fas-online.ru', //your gmail account
           'password' => 'passforma' // your password
       ));

       // Send the mail
       $mail = $smtp->send($to, $headers, $body);*/
       
    }
    public function contactAction()
    {
       // $this->setMenu();
    }
    public function aboutAction()
    {
      //  $this->setMenu();
    }
}