<?php
session_start();
require 'conexion.php';
include 'template.php';

$message = '';
$status = '';

// Consulta para calcular la antigüedad y registrar beneficios
$query = "
    INSERT INTO Beneficios (id_usuario, razon, monto, fechacreacion, usuariocreacion)
    SELECT 
        u.id_usuario,
        'Bono por Antigüedad' AS razon,
        CASE 
            WHEN TIMESTAMPDIFF(YEAR, u.fecha_ingreso, CURDATE()) >= 10 THEN 1000
            WHEN TIMESTAMPDIFF(YEAR, u.fecha_ingreso, CURDATE()) >= 5 THEN 500
            ELSE 0
        END AS monto,
        CURDATE() AS fechacreacion,
        'admin' AS usuariocreacion
    FROM Usuario u
    WHERE TIMESTAMPDIFF(YEAR, u.fecha_ingreso, CURDATE()) >= 1
    AND NOT EXISTS (
        SELECT 1 FROM Beneficios b 
        WHERE b.id_usuario = u.id_usuario AND b.razon = 'Bono por Antigüedad'
    )
";

if ($conn->query($query) === TRUE) {
    $status = 'success';
    $message = 'Beneficios registrados correctamente.';
} else {
    $status = 'error';
    $message = 'Error al registrar beneficios: ' . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Beneficios por Antigüedad</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-3">
        <!-- Mostrar mensaje de éxito o error -->
        <?php if ($status === 'success'): ?>
            <div class="alert alert-success text-center">
                <h4><?= htmlspecialchars($message) ?></h4>
            </div>
        <?php elseif ($status === 'error'): ?>
            <div class="alert alert-danger text-center">
                <h4><?= htmlspecialchars($message) ?></h4>
            </div>
        <?php endif; ?>

        <!-- Contenido adicional si es necesario -->
        <div class="text-center mt-3">
            <a href="registrarBeneficiosAntiguedad.php" class="btn btn-primary">Registrar Nuevos Beneficios</a>
        </div>
    </div>
</body>
</html>