<?php
/**
 * conexion.php
 * Devuelve un objeto mysqli conectado con SSL a Azure MySQL.
 */

function obtenerConexion(): mysqli
{
    $host     = "accespersoneldb.mysql.database.azure.com";
    $user     = "adminUser";
    $password = "admin123+";
    $dbname   = "gestionEmpleados";
    $port     = 3306;

    // Ruta al certificado CA que ya subiste a /home/site/wwwroot/certs/
    $ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

    if (!file_exists($ssl_ca)) {
        die("❌ Certificado SSL no encontrado en: $ssl_ca");
    }

    $conn = mysqli_init();

    // Configura SSL (si no quieres verificar el certificado, pon false en VERIFY_SERVER_CERT)
    mysqli_ssl_set($conn, NULL, NULL, $ssl_ca, NULL, NULL);
    mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

    // Conéctate con la bandera MYSQLI_CLIENT_SSL
    if (! $conn->real_connect(
        $host,
        $user,
        $password,
        $dbname,
        $port,
        NULL,
        MYSQLI_CLIENT_SSL
    )) {
        die("❌ Conexión SSL fallida: " . mysqli_connect_error());
    }

    // Charset UTF8
    mysqli_set_charset($conn, "utf8mb4");

    return $conn;
}
