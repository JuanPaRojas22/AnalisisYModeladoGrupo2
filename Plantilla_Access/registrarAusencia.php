<?php
session_start();
include 'template.php';
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_POST['id_usuario'];
    $fecha = $_POST['fecha'];
    $motivo = $_POST['motivo'];
    $registrado_por = $_SESSION['id_usuario']; // ID del administrador que registra la ausencia

    $query = "INSERT INTO Ausencias (id_usuario, fecha, motivo, registrado_por) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("issi", $id_usuario, $fecha, $motivo, $registrado_por);

    if ($stmt->execute()) {
        echo "<script>alert('Ausencia registrada correctamente.');</script>";
    } else {
        echo "<script>alert('Error al registrar la ausencia.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Ausencia</title>
</head>
<body>
    <h1>Registrar Ausencia</h1>
    <form method="POST" action="">
        <label for="id_usuario">Empleado:</label>
        <select name="id_usuario" required>
            <?php
            $result = $conn->query("SELECT id_usuario, nombre FROM Usuario");
            while ($row = $result->fetch_assoc()) {
                echo "<option value='{$row['id_usuario']}'>{$row['nombre']}</option>";
            }
            ?>
        </select>

        <label for="fecha">Fecha:</label>
        <input type="date" name="fecha" required>

        <label for="motivo">Motivo:</label>
        <input type="text" name="motivo" required>

        <button type="submit">Registrar Ausencia</button>
    </form>
</body>
</html>