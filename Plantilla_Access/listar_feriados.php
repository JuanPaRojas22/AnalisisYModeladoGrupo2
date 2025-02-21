<?php
// Conectar a la base de datos
$conn = new mysqli("localhost", "root", "", "GestionEmpleados");
mysqli_set_charset($conn, "utf8mb4");

// Validar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consultar los feriados
$query = "SELECT id_fecha, nombre_feriado, fecha, tipo_feriado, doble_pago FROM Dias_Feriados ORDER BY fecha ASC";
$result = $conn->query($query);

$feriados = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $feriados[] = $row;
    }
}

// Devolver datos en formato JSON
echo json_encode($feriados);

$conn->close();
?>
