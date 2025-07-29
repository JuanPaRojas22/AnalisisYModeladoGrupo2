<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Verifica que el usuario esté logueado
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión no iniciada']);
    exit;
}

$username = $_SESSION['username'];
$aporte = $_POST['aporte'] ?? '';

if (empty($aporte)) {
    echo json_encode(['success' => false, 'message' => 'Mensaje vacío']);
    exit;
}

// Parámetros de conexión
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

// Ruta al certificado CA para validar SSL
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

// Inicializamos mysqli
$conn = mysqli_init();

// Configuramos SSL
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);


// Intentamos conectar usando SSL (con la bandera MYSQLI_CLIENT_SSL)
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    echo json_encode(['success' => false,'message' => 'Error de conexión: ' . mysqli_connect_error()]);
    exit;
}

// Establecemos el charset
mysqli_set_charset($conn, "utf8mb4");

// Obtener id_usuario desde username
$stmtUser = $conn->prepare("SELECT id_usuario FROM usuario WHERE username = ?");
$stmtUser->bind_param("s", $username);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();

if ($resultUser->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit;
}

$row = $resultUser->fetch_assoc();
$id_usuario = $row['id_usuario'];
$stmtUser->close();

// Insertar en la tabla Aportes
$stmt = $conn->prepare("INSERT INTO aportes (id_usuario, aporte) VALUES (?, ?)");
$stmt->bind_param("is", $id_usuario, $aporte);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Aporte guardado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar']);
}

$stmt->close();
$conn->close();
?>