<?php
// Establecer zona horaria correcta
date_default_timezone_set('America/Costa_Rica');

// Conectar a la base de datos
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

// Inicializamos mysqli
$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8mb4");

// Obtener datos del formulario
$nombre = $_POST['nombre_feriado'];
$fecha = $_POST['fecha']; // Debe venir en formato YYYY-MM-DD
$tipo = $_POST['tipo_feriado'];
$doble_pago = isset($_POST['doble_pago']) ? 1 : 0;

// Insertar el feriado
$query = "INSERT INTO Dias_Feriados (nombre_feriado, fecha, tipo_feriado, doble_pago) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("sssi", $nombre, $fecha, $tipo, $doble_pago);

if ($stmt->execute()) {
    echo "Éxito";
} else {
    echo "Error al agregar feriado";
}

$stmt->close();
$conn->close();
?>