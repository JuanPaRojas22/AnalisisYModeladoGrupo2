<?php
session_start();

// Se destruyen todas las variables de sesión
$_SESSION = array();

// Se verifica si se usa cookies en la sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, se destruye la sesión
session_destroy();

// Se redirige al usuario a la página de inicio de sesión
header("Location: login.php");
exit();
?>