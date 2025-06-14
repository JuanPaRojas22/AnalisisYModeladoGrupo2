<?php
/**
 * conexion.php
 * Conexión segura SSL a Azure MySQL usando MySQLi (orientado a objetos)
 */

<?php
function obtenerConexion(): mysqli
{
    $host     = "accespersoneldb.mysql.database.azure.com";
    $user     = "adminUser";
    $password = "admin123+";
    $dbname   = "gestionEmpleados";
    $port     = 3306;

    $ssl_ca = '/home/site/wwwroot/certs/fullchain.pem';

    if (!file_exists($ssl_ca)) {
        die("❌ Certificado SSL no encontrado en: $ssl_ca");
    }

    $conn = mysqli_init();

    // Primero las opciones
    $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);

    // Luego el certificado
    $conn->ssl_set(null, null, $ssl_ca, null, null);

    if (!$conn->real_connect(
        $host,
        $user,
        $password,
        $dbname,
        $port,
        null,
        MYSQLI_CLIENT_SSL
    )) {
        die("❌ Conexión SSL fallida: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");

    return $conn;
}
?>

?>
