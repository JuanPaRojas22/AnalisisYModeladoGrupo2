<?php
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=reporte_ins.xls");

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "gestionempleados");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener los datos de reporte_ins
$sql = "SELECT * FROM reporte_ins";
$resultado = $conexion->query($sql);

// Generar la tabla en formato Excel
echo "<table border='1'>";
echo "<tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>Fecha Nacimiento</th>
        <th>Teléfono</th>
        <th>Correo</th>
        <th>Sexo</th>
        <th>Estado Civil</th>
        <th>Nacionalidad</th>
        <th>Jornada</th>
        <th>Días</th>
        <th>Horas</th>
        <th>Salario</th>
        <th>Ocupación</th>
        <th>Descripción Ocupación</th>
        <th>Dirección</th>
        <th>Salario Base</th>
        <th>Salario Neto</th>
        <th>Tipo de Quincena</th>
        <th>Mes</th>
        <th>Año</th>
    </tr>";

while ($fila = $resultado->fetch_assoc()) {
    echo "<tr>
            <td>{$fila['id_usuario']}</td>
            <td>{$fila['nombre']}</td>
            <td>{$fila['apellido']}</td>
            <td>{$fila['fecha_nacimiento']}</td>
            <td>{$fila['telefono']}</td>
            <td>{$fila['correo']}</td>
            <td>{$fila['sexo']}</td>
            <td>{$fila['estado_civil']}</td>
            <td>{$fila['nacionalidad']}</td>
            <td>{$fila['jornada']}</td>
            <td>{$fila['dias']}</td>
            <td>{$fila['hrs']}</td>
            <td>{$fila['salario']}</td>
            <td>{$fila['ocupacion']}</td>
            <td>{$fila['descripcion_ocupacion']}</td>
            <td>{$fila['direccion_domicilio']}</td>
            <td>{$fila['salario_base']}</td>
            <td>{$fila['salario_neto']}</td>
            <td>{$fila['tipo_quincena']}</td>
            <td>{$fila['mes']}</td>
            <td>{$fila['anio']}</td>
        </tr>";
}
echo "</table>";

// Cerrar la conexión
$conexion->close();
?>
