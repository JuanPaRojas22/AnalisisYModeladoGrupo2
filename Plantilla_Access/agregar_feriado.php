<?php
// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "GestionEmpleados");
mysqli_set_charset($conn, "utf8mb4");

// Validar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener datos del formulario
$nombre = $_POST['nombre_feriado'];
$fecha = $_POST['fecha'];
$tipo = $_POST['tipo_feriado'];
$doble_pago = isset($_POST['doble_pago']) ? 1 : 0; // Si está marcado, es 1

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
