<?php
require 'conexion.php';
session_start();

$query = "SELECT Bonos.id_bono, Usuario.nombre, Bonos.razon, Bonos.monto_total, Bonos.fecha_aplicacion 
          FROM Bonos 
          INNER JOIN Usuario ON Bonos.id_usuario = Usuario.id_usuario";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bonos Aplicados</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2 class="text-center mt-4">Bonos Aplicados</h2>
    <table class="table table-bordered mt-4">
        <thead>
            <tr>
                <th>ID Bono</th>
                <th>Empleado</th>
                <th>Razón</th>
                <th>Monto</th>
                <th>Fecha Aplicación</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id_bono']; ?></td>
                    <td><?php echo $row['nombre']; ?></td>
                    <td><?php echo $row['razon']; ?></td>
                    <td>₡<?php echo number_format($row['monto_total'], 2); ?></td>
                    <td><?php echo $row['fecha_aplicacion']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="text-center mt-4">
        <a href="MostrarUsuarios.php" class="btn btn-secondary">Volver</a>
    </div>
</div>

<script src="assets/js/jquery.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
