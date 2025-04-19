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

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "gestionempleados");

if ($conexion->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión']);
    exit;
}

// Obtener id_usuario desde username
$stmtUser = $conexion->prepare("SELECT id_usuario FROM usuario WHERE username = ?");
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
$stmt = $conexion->prepare("INSERT INTO aportes (id_usuario, aporte) VALUES (?, ?)");
$stmt->bind_param("is", $id_usuario, $aporte);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Aporte guardado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar']);
}

$stmt->close();
$conexion->close();
?>
