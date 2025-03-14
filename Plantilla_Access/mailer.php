<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/PHPMailer.php';
require 'src/SMTP.php';
require 'src/Exception.php';

function enviarCorreo($correo_destino, $asunto, $mensaje) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'paulacorderosegura@gmail.com'; //  correo
        $mail->Password = 'svls mwzw milm bwkq'; // contraseña  de aplicación
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('paulacorderosegura@gmail.com', 'Sistema de Vacaciones'); //con el que se envia
        $mail->addAddress($correo_destino, 'User'); //el destinatario que seria el usuario que solicita la vacacion

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $mensaje;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>
