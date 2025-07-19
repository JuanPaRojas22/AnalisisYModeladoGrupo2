<?php
require 'conexion.php';
require 'mailer.php';

$conn = obtenerConexion(); 

$mensaje_resultado = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];

    $stmt = $conn->prepare("SELECT * FROM usuario WHERE correo_electronico = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        $token = bin2hex(random_bytes(50));
        $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

        $stmt = $conn->prepare("UPDATE usuario SET token_recuperacion = ?, token_expira = ? WHERE correo_electronico = ?");
        $stmt->bind_param("sss", $token, $expira, $correo);
        $stmt->execute();

        $enlace = "https://accesspersonel-fue0gkhkabeahsgd.canadacentral-01.azurewebsites.net/reset_password.php?token=$token"; // Cambia por tu dominio real
        $asunto = "Recuperación de contraseña";
        $mensaje = "Hola, haz clic en el siguiente enlace para cambiar tu contraseña:<br><a href='$enlace'>$enlace</a>";

        if (enviarCorreo($correo, $asunto, $mensaje)) {
            $mensaje_resultado = "✅ Revisa tu correo para restablecer tu contraseña.";
        } else {
            $mensaje_resultado = "❌ Error al enviar el correo. Intenta más tarde.";
        }
    } else {
        $mensaje_resultado = "⚠️ El correo no está registrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resultado de recuperación</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <style>
        body {
            height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .background-blur {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('assets/img/loginbg.jpg');
            background-size: cover;
            background-position: center;
            filter: blur(4px);
            z-index: -1;
        }

        .form-box {
            background: rgba(236, 231, 231, 0.2);
            backdrop-filter: blur(75px);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .form-box h2 {
            color: white;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .form-box p {
            color: white;
            font-size: 16px;
            font-weight: bold;
        }

        .btn-theme {
            margin-top: 20px;
            background: rgba(95, 94, 94, 0.3);
            border: none;
            color: white;
            font-weight: bold;
        }

        .btn-theme:hover {
            background: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="background-blur"></div>
    <div class="form-box">
        <meta charset="UTF-8">
        <h2>Recuperación de contraseña</h2>
        <p><?php echo $mensaje_resultado; ?></p>
<a href="https://mail.google.com" target="_blank" class="btn btn-theme btn-block">Ir a Gmail</a>
       <a href="login.php" class="btn btn-theme btn-block">Volver al inicio</a>

    </div>

    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>

