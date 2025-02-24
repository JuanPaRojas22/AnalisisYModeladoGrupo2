<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "gestionempleados");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener los datos
$sql = "SELECT id_reporte_caja, id_usuario, cedula_caja, salario_colones, fecha_generacion, link_archivo FROM Reporte_Caja";
$resultado = $conexion->query($sql);

// Configurar la cabecera para descargar el archivo Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=reporte_caja.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Iniciar la tabla y los encabezados
echo "<table border='1'>";
echo "<tr>
        <th>ID Reporte Caja</th>
        <th>ID Usuario</th>
        <th>Cédula Caja</th>
        <th>Salario (Colones)</th>
        <th>Fecha de Generación</th>
        <th>Link Archivo</th>
      </tr>";

// Si hay datos, los imprimimos
if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        echo "<tr>
                <td>{$fila['id_reporte_caja']}</td>
                <td>{$fila['id_usuario']}</td>
                <td>{$fila['cedula_caja']}</td>
                <td>" . number_format($fila['salario_colones'], 2) . "</td>
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
