<?php
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

if (!file_exists($ssl_ca)) {
    die("❌ Certificado CA no encontrado en: $ssl_ca\n");
}

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, $ssl_ca, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);

if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("❌ Error de conexión SSL: " . mysqli_connect_error());
}

echo "✅ Conexión SSL exitosa.\n";
$conn->close();
?>
