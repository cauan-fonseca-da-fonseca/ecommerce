<?php

namespace Hcode;

use Rain\Tpl;
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require 'vendor/autoload.php';

class Mailer {

    const USERNAME = "cauanfonsecaff@gmail.com";
    const PASSWORD = "nckfwjmlsrjcswot";
    const NAME_FROM = "Cauan";

    private $mail;

    public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
    {

        $config = array(
            "tpl_dir"   =>  $_SERVER["DOCUMENT_ROOT"]. "/views/email/",
            "cache_dir" =>  $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
            "debug"     => false
        );

        Tpl::configure($config);

        $tpl = new Tpl;

        foreach($data as $key => $value) {
            $tpl->assign($key, $value);
        }

        $html = $tpl->draw($tplName, true);

        //Create an instance; passing `true` enables exceptions
        $this->mail = new PHPMailer(true);

        try {
            //Server settings
            $this->mail->SMTPDebug = 4;                     //Enable verbose debug output
            $this->mail->isSMTP();                                            //Send using SMTP
            $this->mail->Host = 'smtp.gmail.com'; 
            $this->mail->Debugoutput = 'html';                    //Set the SMTP server to send through
            $this->mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $this->mail->Username   = Mailer::USERNAME;                //SMTP username
            $this->mail->Password   = Mailer::PASSWORD;                              //SMTP password
            $this->mail->SMTPSecure = "tls";            //Enable implicit TLS encryption
            $this->mail->Port       = 587; 
            $this->mail->msgHTML($html);                                   //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);
            $this->mail->addAddress($toAddress, $toName);
            $this->mail->addReplyTo(Mailer::USERNAME, Mailer::NAME_FROM);


            //Content
            $this->mail->isHTML(true);                                  //Set email format to HTML
            $this->mail->Subject =  $subject;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}";
        }
    }

    public function send()
    {
        return $this->mail->send();
    }

}