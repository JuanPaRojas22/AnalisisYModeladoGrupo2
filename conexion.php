<?php
function obtenerConexion(): mysqli
{
    $host     = "accespersoneldb.mysql.database.azure.com";
    $user     = "adminUser";
    $password = "admin123+";
    $dbname   = "gestionEmpleados";
    $port     = 3306;

    $ssl_ca = '/home/site/wwwroot/certs/fullchain.pem';

    //if (!file_exists($ssl_ca)) {
        //die("❌ Certificado SSL no encontrado en: $ssl_ca");
    //}

    $conn = mysqli_init();

    // Establecer certificado CA
    $conn->ssl_set(null, null, $ssl_ca, null, null);

    // Deshabilitar verificación del certificado del servidor (solo para probar)
    $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

    if (!$conn->real_connect(
        $host,
        $user,
        $password,
        $dbname,
        $port,
        null,
        MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT
    )) {
        die("❌ Conexión SSL fallida: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    return $conn;
}
?>
