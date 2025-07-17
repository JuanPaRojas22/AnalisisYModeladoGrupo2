<?php
require 'conexion.php';

$conn = obtenerConexion(); 


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE usuario SET password = ?, token_recuperacion = NULL, token_expira = NULL WHERE token_recuperacion = ?");
    $stmt->bind_param("ss", $password_hash, $token);

    if ($stmt->execute()) {
        $mensaje = "✅ Contraseña actualizada correctamente.<br>Ahora podés iniciar sesión con tu nueva contraseña.";
    } else {
        $mensaje = "❌ Error al actualizar la contraseña. Intentalo de nuevo.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contraseña actualizada</title>
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
        <h2>Resultado</h2>
        <p><?php echo $mensaje; ?></p>
        <a href="login.php" class="btn btn-theme btn-block">Volver al inicio</a>
    </div>

    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
