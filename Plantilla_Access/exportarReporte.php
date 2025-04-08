<?php
session_start();
require 'conexion.php';

// Consulta para obtener los datos
$query = "SELECT u.nombre AS empleado, COUNT(a.id_ausencia) AS total_ausencias, MONTH(a.fecha) AS mes
          FROM Ausencias a
          JOIN Usuario u ON a.id_usuario = u.id_usuario
          GROUP BY u.nombre, MONTH(a.fecha)";
$result = $conn->query($query);

// Configurar encabezados para la descarga
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=reporte_ausencias.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Imprimir encabezados de las columnas
echo "Empleado\tTotal Ausencias\tMes\n";

// Imprimir los datos
while ($row = $result->fetch_assoc()) {
    echo "{$row['empleado']}\t{$row['total_ausencias']}\t{$row['mes']}\n";
}
?>