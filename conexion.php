<?php
/**
 * conexion.php
 * Conexión segura SSL a Azure MySQL usando MySQLi (orientado a objetos)
 */

function obtenerConexion(): mysqli
{
    $host     = "accespersoneldb.mysql.database.azure.com";
    $user     = "adminUser";
    $password = "admin123+";
    $dbname   = "gestionEmpleados";
    $port     = 3306;

    // Ruta al certificado raíz
    $ssl_ca = '/home/site/wwwroot/certs/DigiCertGlobalRootG2.crt.pem';

    if (!file_exists($ssl_ca)) {
        die("❌ Certificado SSL no encontrado en: $ssl_ca");
    }

    // Inicializa MySQLi
    $conn = mysqli_init();

    // Establece el certificado CA para SSL
    $conn->ssl_set(null, null, null, null, null);

    // Opcional: verificar certificado (true para producción segura)
    $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);

    // Intenta conectar con SSL
    if (!$conn->real_connect(    $host,
    $username,
    $password,
    $database,
    3306,
    null,
    MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT)) {
        die("❌ Conexión SSL fallida: " . $conn->connect_error);
    }

    // Charset
    $conn->set_charset("utf8mb4");

    return $conn;
}
?>
