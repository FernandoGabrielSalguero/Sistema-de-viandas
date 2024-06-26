<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'contacto@ilmanagastronomia.com';
        $mail->Password = 'Helader@1';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom('contacto@ilmanagastronomia.com', 'Il Mana Gastronomia');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        echo 'Mensaje enviado';
    } catch (Exception $e) {
        echo "Mensaje no enviado. Mailer Error: {$mail->ErrorInfo}";
    }
}
