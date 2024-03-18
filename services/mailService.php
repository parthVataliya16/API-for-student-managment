<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

class SendMail
{
    private $mailSend;

    public function __construct()
    {
        global $mail;
        
        $this->mailSend = new PHPMailer();
        $this->mailSend->isSMTP();
        $this->mailSend->Host = $mail["host"];
        $this->mailSend->SMTPAuth = $mail["SMTPAuth"];
        $this->mailSend->Port = $mail["port"];
        $this->mailSend->Username = $mail["username"];
        $this->mailSend->Password = $mail["password"];
    }

    public function sendMail($sendMailFrom, $sendMailTo, $subject, $body)
    {
        $this->mailSend->setFrom($sendMailFrom);
        $this->mailSend->addAddress($sendMailTo);
        
        $this->mailSend->isHTML(true);
        $this->mailSend->Subject = $subject;
        $this->mailSend->Body = $body;
        $this->mailSend->send();
    }
}
?>