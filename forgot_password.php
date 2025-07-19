<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
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

        .form-recover {
            background: rgba(236, 231, 231, 0.2);
            backdrop-filter: blur(75px);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 100%;
            max-width: 400px;
        }

        .form-recover h2 {
            color: white;
            margin-bottom: 20px;
            font-weight: bold;
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
            background: rgba(95, 94, 94, 0.3);
            border: none;
            color: white;
            font-weight: bold;
        }

        .btn-theme:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        a {
            color: white;
            font-weight: bold;
            display: inline-block;
            margin-top: 15px;
        }

    </style>
</head>

<body>
    <div class="background-blur"></div>

    <form class="form-recover" action="enviar_recuperacion.php" method="POST">
        <h2>¿Olvidaste tu contraseña?</h2>
        <input type="email" name="correo" class="form-control" placeholder="Correo electrónico" required>
        <br>
        <button type="submit" class="btn btn-theme btn-block">Enviar enlace de recuperación</button>
        <a href="login.php">← Volver al inicio</a>
    </form>

    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>

</html>
