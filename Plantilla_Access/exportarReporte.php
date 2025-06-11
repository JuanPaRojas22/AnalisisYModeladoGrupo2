<?php
session_start();
require 'conexion.php';

// Consulta para obtener los datos, incluyendo justificada
$query = "
    SELECT 
      u.nombre               AS empleado, 
      COUNT(a.id_ausencia)   AS total_ausencias, 
      MONTH(a.fecha)         AS mes,
      a.justificada
    FROM Ausencias a
    JOIN Usuario u 
      ON a.id_usuario = u.id_usuario
    GROUP BY 
      u.nombre, 
      MONTH(a.fecha), 
      a.justificada
    ORDER BY 
      mes ASC, 
      empleado ASC, 
      a.justificada ASC
";
$result = $conn->query($query);

// Configurar encabezados para la descarga XLS
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=reporte_ausencias.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Imprimir encabezados de columnas
echo "Empleado\tTotal Ausencias\tMes\tJustificada\n";

// Imprimir los datos fila por fila
while ($row = $result->fetch_assoc()) {
    echo 
      "{$row['empleado']}\t" .
      "{$row['total_ausencias']}\t" .
      "{$row['mes']}\t" .
      "{$row['justificada']}\n";
}
exit;
?>
