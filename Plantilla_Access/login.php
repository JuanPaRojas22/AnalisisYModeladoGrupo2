<?php
// Se hace la conexión a la base de datos
$conn = new mysqli("127.0.0.1", "adminUser", "admin123+", "gestionEmpleados");
mysqli_set_charset($conn, "utf8mb4");

// Se valida la conexión a la base de datos
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Inicializar sesión y variables de error
session_start();
$error_message = "";

// Inicializar variables de intentos y bloqueo si no existen
if (!isset($_SESSION['intentos_fallidos'])) {
    $_SESSION['intentos_fallidos'] = 0;
    $_SESSION['bloqueado_hasta'] = null;
}

// Verificar si hay un bloqueo activo
if ($_SESSION['bloqueado_hasta'] !== null && time() < $_SESSION['bloqueado_hasta']) {
    $tiempoRestante = $_SESSION['bloqueado_hasta'] - time();
    $minutos = floor($tiempoRestante / 60);
    $segundos = $tiempoRestante % 60;
    $error_message = "Cuenta bloqueada. Intente nuevamente en $minutos minutos y $segundos segundos.";
} else {
    // Si el método de solicitud es POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (!empty($username) && !empty($password)) {
            $sql = "SELECT * FROM USUARIO WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($_SESSION['bloqueado_hasta'] !== null && time() < $_SESSION['bloqueado_hasta']) {
    $tiempoRestante = $_SESSION['bloqueado_hasta'] - time();
    $minutos = floor($tiempoRestante / 60);
    $segundos = $tiempoRestante % 60;
    $error_message = "Cuenta bloqueada. Intente nuevamente en $minutos minutos y $segundos segundos.";
} else {
    // Proceso de login
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
        if (isset($usuario['password']) && password_verify($password, $usuario['password'])) {
            // Resetea los intentos fallidos si el login es exitoso
            $reset_sql = "UPDATE USUARIO SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE username = ?";
            $reset_stmt = $conn->prepare($reset_sql);
            $reset_stmt->bind_param('s', $username);
            $reset_stmt->execute();

            // Login correcto: asigna datos y redirige
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['username'] = $usuario['username'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['id_rol'] = $usuario['id_rol'];
            $_SESSION['id_departamento'] = $usuario['id_departamento']; 

            $_SESSION['logged_in'] = true;

            header("Location: index.php?login=success&username=" . urlencode($usuario['nombre']));
            exit();
        } else {
            $_SESSION['intentos_fallidos']++;

            if ($_SESSION['intentos_fallidos'] >= 5) {
                $_SESSION['bloqueado_hasta'] = time() + (5 * 60); // bloquea por 5 minutos
                $error_message = "Cuenta bloqueada por demasiados intentos. Intente más tarde.";
            } else {
                $error_message = "Contraseña incorrecta. Intento {$_SESSION['intentos_fallidos']} de 5.";
            }
        }
    } else {
        $error_message = "Usuario no encontrado.";
    }
}

        } else {
            $error_message = "Por favor, completa todos los campos.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>Accces Personnel</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />

    <!-- Custom styles for this template -->
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<style>
    body {

        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .container {
        width: 100%;
        max-width: 400px;
    }

    .form-login {
        background: rgba(236, 231, 231, 0.2);
        backdrop-filter: blur(75px);
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
        margin-left: 15%;
    }

    .form-login h2 {
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

    .login-wrap label {
        color: white;
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

    .login-social-link button {
        background: rgba(0, 0, 0, 0.3);
        border: none;
        color: white;
        font-weight: bold;

    }

    .login-social-link button:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    .registration a {
        color: rgb(63, 62, 62);
        font-weight: bold;
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
        /* Aplica el filtro de desenfoque */
        z-index: -1;
        /* Coloca el div debajo del contenido */
    }
</style>

<!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
<div class="background-blur"></div>
<div id="login-page">
    <div class="container">

        <form class="form-login" action="login.php" method="post">
            <h2 class="form-login-heading">BIENVENIDO</h2>
            <div class="login-wrap">

                <input type="text" name="username" class="form-control" placeholder="User" autofocus>
                <br>
                <input type="password" name="password" class="form-control" placeholder="Password">
                 <!-- Enlace para recuperar contraseña -->
               <div style="margin-top: 10px; text-align: center;">
    <a href="forgot_password.php" style="color: white; font-weight: bold; display: inline-block; width: 100%;">
        ¿Olvidaste tu contraseña?
    </a>
</div>
                <!-- <label class="checkbox">
                        <span class="pull-right">
                            <a data-toggle="modal" href="login.php#myModal"> Forgot Password?</a>
                        </span>
                    </label> -->
                <br>
                <button class="btn btn-theme btn-block" type="submit"><i class="fa fa-lock"></i> ENTRAR</button>
                <?php if ($error_message): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
                <hr>


                <div class="registration" style="font-weight: bold;">
                    ¿No tienes cuenta?<br />
                    <a class="" href="createUser.php" style="font-weight: bold;">
                        Registrate aquí
                    </a>
                </div>
        </form>
    </div>

<!-- Modal -->
<div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Forgot Password ?</h4>
            </div>
            <div class="modal-body">
                <p>Enter your e-mail address below to reset your password.</p>
                <input type="text" name="email" placeholder="Email" autocomplete="off"
                    class="form-control placeholder-no-fix">

            </div>
            <div class="modal-footer">
                <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
                <button class="btn btn-theme" type="button">Submit</button>
            </div>
        </div>
    </div>
</div>
<!-- modal -->

</form>

</div>
</div>

<!-- js placed at the end of the document so the pages load faster -->
<script src="assets/js/jquery.js"></script>
<script src="assets/js/bootstrap.min.js"></script>

<!--BACKSTRETCH-->
<!-- You can use an image of whatever size. This script will stretch to fit in any screen size.-->
<script type="text/javascript" src="assets/js/jquery.backstretch.min.js"></script>
<script>
    $.backstretch("assets/img/loginbg.jpg", { speed: 500 });
</script>


</body>

</html>