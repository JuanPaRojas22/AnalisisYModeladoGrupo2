<?php
require 'conexion.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('<h3 style="color:red;text-align:center;">Token inválido.</h3>');
}

$stmt = $conn->prepare("SELECT * FROM usuario WHERE token_recuperacion = ? AND token_expira > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    die('<h3 style="color:red;text-align:center;">Token expirado o inválido.</h3>');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
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

        .form-control {
            background: rgba(255, 255, 255, 0.5);
            border: none;
            border-radius: 5px;
            color: black;
            font-weight: bold;
        }

        .form-control::placeholder {
            color: black;
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
    <form action="actualizar_password.php" method="POST" class="form-box">
        <h2>Restablecer contraseña</h2>
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
        <input type="password" name="password" class="form-control" placeholder="Nueva contraseña" required>
        <button type="submit" class="btn btn-theme btn-block">Actualizar contraseña</button>
    </form>

    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
