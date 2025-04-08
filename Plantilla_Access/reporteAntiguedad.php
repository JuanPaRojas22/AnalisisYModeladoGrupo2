<?php
session_start();
require 'conexion.php';
include 'template.php';
// Consulta para calcular la antigüedad y obtener los beneficios
$query = "
    SELECT 
        u.id_usuario,
        CONCAT(u.nombre, ' ', u.apellido) AS nombre_completo,
        u.fecha_ingreso,
        TIMESTAMPDIFF(YEAR, u.fecha_ingreso, CURDATE()) AS antiguedad_anios,
        TIMESTAMPDIFF(MONTH, u.fecha_ingreso, CURDATE()) % 12 AS antiguedad_meses,
        b.razon AS beneficio,
        b.monto AS monto_beneficio,
        b.fechacreacion AS fecha_beneficio
    FROM Usuario u
    LEFT JOIN Beneficios b ON u.id_usuario = b.id_usuario AND b.razon = 'Bono por Antigüedad'
    WHERE u.fecha_ingreso IS NOT NULL
    ORDER BY antiguedad_anios DESC, antiguedad_meses DESC
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Antigüedad Laboral</title>
    <form method="POST" action="exportarAntiguedad.php">
    <button type="submit" class="btn btn-success">Exportar Reporte</button>
</form>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Reporte de Antigüedad Laboral</h2>
        <form method="POST" action="exportarAntiguedad.php">
    <button type="submit" class="btn btn-success">Exportar Reporte</button>
</form>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>Fecha de Ingreso</th>
                    <th>Antigüedad (Años)</th>
                    <th>Antigüedad (Meses)</th>
                    <th>Beneficio</th>
                    <th>Monto del Beneficio</th>
                    <th>Fecha del Beneficio</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id_usuario'] ?></td>
                        <td><?= htmlspecialchars($row['nombre_completo']) ?></td>
                        <td><?= $row['fecha_ingreso'] ?></td>
                        <td><?= $row['antiguedad_anios'] ?></td>
                        <td><?= $row['antiguedad_meses'] ?></td>
                        <td><?= htmlspecialchars($row['beneficio'] ?? 'N/A') ?></td>
                        <td><?= $row['monto_beneficio'] ?? 'N/A' ?></td>
                        <td><?= $row['fecha_beneficio'] ?? 'N/A' ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>