<?php
$conn = new mysqli("localhost", "root", "", "GestionEmpleados");
mysqli_set_charset($conn, "utf8mb4");

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$id = $_POST['id_fecha'];

$query = "DELETE FROM Dias_Feriados WHERE id_fecha=?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

$conn->close();
?>
