<?php
session_start();
require 'conexion.php';
include 'template.php';

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
    echo "Beneficios registrados correctamente.";
} else {
    echo "Error al registrar beneficios: " . $conn->error;
}
?>