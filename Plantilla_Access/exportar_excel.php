<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "gestionempleados");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Establecer la codificación UTF-8
$conexion->set_charset("utf8");

// Consulta para obtener los datos de las tablas usuario, planilla, nacionalidades, ocupaciones y departamento
$sql = "SELECT u.id_usuario, u.nombre, u.apellido, u.correo_electronico, u.numero_telefonico, u.fecha_nacimiento, u.sexo, u.estado_civil, 
               n.pais AS nacionalidad, o.nombre_ocupacion, p.jornada, p.hrs, p.salario_base, p.salario_neto, p.codigo_INS,
               u.direccion_domicilio, p.tipo_quincena, d.nombre AS departamento 
        FROM usuario u
        JOIN planilla p ON u.id_usuario = p.id_usuario
        JOIN nacionalidades n ON u.id_nacionalidad = n.id_nacionalidad
        LEFT JOIN ocupaciones o ON u.id_ocupacion = o.id_ocupacion
        LEFT JOIN departamento d ON u.id_departamento = d.id_departamento"; // JOIN a departamento agregado

$resultado = $conexion->query($sql);

// Establecer cabeceras para exportar como Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=reporte_ins.xls");

// Crear la tabla en formato Excel
echo "<table border='1'>";
echo "<tr>
 <th>Codigo</th>
        <th>ID</th>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>Fecha Nacimiento</th>
        <th>Telefono</th>
        <th>Correo Electronico</th>
        <th>Sexo</th>
        <th>Estado Civil</th>
        <th>Nacionalidad</th>
        <th>Jornada</th>
        <th>Horas</th>
        <th>Salario Base</th>
        <th>Salario Neto</th>
        <th>Ocupacion</th>
        <th>Departamento</th>
        <th>Direccion</th>
        <th>Tipo de Quincena</th>
        
    </tr>";

// Recorrer los resultados y generar las filas de la tabla en Excel
while ($fila = $resultado->fetch_assoc()) {
    echo "<tr>
    <td>{$fila['codigo_INS']}</td>
            <td>{$fila['id_usuario']}</td>
            <td>{$fila['nombre']}</td>
            <td>{$fila['apellido']}</td>
            <td>{$fila['fecha_nacimiento']}</td>
            <td>{$fila['numero_telefonico']}</td>
            <td>{$fila['correo_electronico']}</td>
            <td>{$fila['sexo']}</td>
            <td>{$fila['estado_civil']}</td>
            <td>{$fila['nacionalidad']}</td>
            <td>{$fila['jornada']}</td>
            <td>{$fila['hrs']}</td>
            <td>{$fila['salario_base']}</td>
            <td>{$fila['salario_neto']}</td>
            <td>{$fila['nombre_ocupacion']}</td>
            <td>{$fila['departamento']}</td>
            <td>{$fila['direccion_domicilio']}</td>
            <td>{$fila['tipo_quincena']}</td>
            
        </tr>";
}

echo "</table>"; 

// Cerrar la conexión
$conexion->close();
?>

