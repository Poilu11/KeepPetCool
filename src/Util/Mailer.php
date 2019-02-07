<?php

namespace App\Util;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    private $password;

    public function __construct($password){
        $this->password = $password;
    }

    public function send(string $email, string $body, string $object = 'KeepPetCool', string $firstname = 'Anonymous', string $lastname = 'Anonymous'){
        
        // https://github.com/PHPMailer/PHPMailer
        $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
        try {
        //Server settings
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();                                      // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                               // Enable SMTP authentication
        $mail->Username = 'keeppetcool@gmail.com';                 // SMTP username
        $mail->Password = $this->password;                           // SMTP password
        $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                    // TCP port to connect to
        
        //Recipients
        $mail->setFrom('keeppetcool@gmail.com', 'KeepPetCool');
        $mail->addAddress($email, $firstname . ' ' . $lastname);
        
        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $object;
        $mail->Body    = $body;
        $mail->AltBody = str_replace($body, '<br>', ' ');
        
        $mail->send();
            return true;
        } catch (Exception $e) {
            // dump('Le message n\'a pu être envoyé. Message d\'erreur : ', $mail->ErrorInfo);
        }
        
    }
}