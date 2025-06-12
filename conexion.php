<?php
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

// Ruta al certificado CA
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

$conn = mysqli_init();

// Validar que el archivo existe
if (!file_exists($ssl_ca)) {
    die(" Certificado no encontrado en: $ssl_ca");
}

// Configurar SSL
mysqli_ssl_set($conn, NULL, NULL, $ssl_ca, NULL, NULL);

// **IMPORTANTE**: dejar verificación activada
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);

// Conexión segura obligatoria
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error al conectar por SSL: " . mysqli_connect_error());
}

// Charset
mysqli_set_charset($conn, "utf8mb4");

// echo "✅ Conexión SSL exitosa";
?>
