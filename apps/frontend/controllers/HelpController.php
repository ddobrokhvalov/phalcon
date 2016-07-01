<?php
namespace Multiple\Frontend\Controllers;
use Phalcon\Mvc\Controller;
use Multiple\Library\PHPMailer\PHPMailerAutoload;

class HelpController extends Controller
{
    public function faqAction()
    {
       // $this->setMenu();
    }
    public function contactAction()
    {
       // $this->setMenu();
    }
    public function aboutAction()
    {
      //  $this->setMenu();
    }
    
    public function sendMailFromContactAction() {
        $name = $this->request->getPost('sender-name');
        $phone = $this->request->getPost('sender-phone');
        $email = $this->request->getPost('sender-email');
        $message = $this->request->getPost('sender-message');
        if (strlen($email)) {
            require_once __DIR__ . '/../../library/PHPMailer/PHPMailerAutoload.php';
            $mail = new \PHPMailer();

            //$mail->SMTPDebug = 3;                               // Enable verbose debug output

            $mail->isSMTP();                                      // Set mailer to use SMTP
            $mail->Host = 'smtp.yandex.ru';                       // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'forma@fas-online.ru';                 // SMTP username
            $mail->Password = 'passforma';                           // SMTP password
            $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port = 587;                                    // TCP port to connect to
            $stat = '';
            /*$mail->Debugoutput = function($str, $level) {
                $stat .= "$level: $str\n";
            };*/
            $mail->Timeout = 10;
            $mail->CharSet = 'utf-8';
            $mail->setFrom('forma@fas-online.ru', 'Fas-online');
            $mail->addAddress('forma@fas-online.ru');     // Add a recipient
            //$mail->addAddress('ellen@example.com');               // Name is optional
            //$mail->addReplyTo('info@example.com', 'Information');
            //$mail->addCC('cc@example.com');
            //$mail->addBCC('bcc@example.com');

            //$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
            $mail->isHTML(true);                                  // Set email format to HTML

            $mail->Subject = 'Форма fas-online контакты';
            $mail->Body = 'Имя: ' . $name . '<br/>';
            $mail->Body .= 'Телефон: ' . $phone . '<br/>';
            $mail->Body .= 'Имейл: ' . $email . '<br/>';
            $mail->Body .= 'Сообщение: ' . $message . '<br/>';
            //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

           if(!$mail->send()) {
               $stat .= 'Невозможно отправить сообщение.';
               $stat .= 'Mailer Error: ' . $mail->ErrorInfo;
               $this->flashSession->error($stat);
           } else {
                $stat .= 'Сообщение успешно отправлено';
                $this->flashSession->success($stat);
           }
       } else {
           $this->flashSession->error("Имейл не может быть пустым");
       }
       return $this->dispatcher->forward(array(
            'module' => 'frontend',
            'controller' => 'help',
            'action' => 'contact',
            'params' => []
        ));
    }
}