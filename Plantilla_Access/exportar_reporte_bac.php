<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "gestionempleados");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener los datos
$sql = "SELECT id_reporte_bac, id_usuario, cedula_bac, salario_neto, fecha_generacion, link_archivo FROM Reporte_Bac";
$resultado = $conexion->query($sql);

// Configurar la cabecera para la descarga del archivo Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=reporte_bac.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Iniciar la tabla y los encabezados
echo "<table border='1'>";
echo "<tr>
        <th>ID Reporte BAC</th>
        <th>ID Usuario</th>
        <th>Cédula BAC</th>
        <th>Salario Neto</th>
        <th>Fecha de Generación</th>
        <th>Link Archivo</th>
      </tr>";

// Si hay datos, los imprimimos
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>
                <td>{$fila['id_reporte_bac']}</td>
                <td>{$fila['id_usuario']}</td>
                <td>{$fila['cedula_bac']}</td>
                <td>" . number_format($fila['salario_neto'], 2) . "</td>
                <td>{$fila['fecha_generacion']}</td>
                <td><a href='{$fila['link_archivo']}' target='_blank'>Ver Archivo</a></td>
              </tr>";
    }
} else {
    // Si no hay datos, generamos una fila vacía con los mismos encabezados
    echo "<tr>
            <td colspan='6' style='text-align: center;'>No hay datos disponibles</td>
          </tr>";
}

echo "</table>";

// Cerrar conexión
$conexion->close();
?>