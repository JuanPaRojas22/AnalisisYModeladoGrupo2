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
        $mail->Username = 'paulacorderosegura@gmail.com';
        $mail->Password = 'svls mwzw milm bwkq';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('paulacorderosegura@gmail.com', 'Sistema de Vacaciones');
        $mail->addAddress($correo_destino, 'User');

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->AltBody = strip_tags($mensaje); // Texto plano si bloquean HTML
        $mail->Body = $mensaje;

        $mail->send();
        return true;
    } catch (Exception $e) {
        echo "❌ Error al enviar correo: " . $mail->ErrorInfo;
        return false;
    }
}
?>
