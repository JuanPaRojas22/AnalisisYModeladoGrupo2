<?php
// filepath: c:\xampp\htdocs\AnalisisYModeladoGrupo2\Plantilla_Access\exportar_ccss_excel.php

// Conexión a la base de datos
// Parámetros de conexión
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

// Ruta al certificado CA para validar SSL
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

// Inicializamos mysqli
$conn = mysqli_init();

// Configuramos SSL
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);


// Intentamos conectar usando SSL (con la bandera MYSQLI_CLIENT_SSL)
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecemos el charset
mysqli_set_charset($conn, "utf8mb4");

// Consulta para obtener los datos de las tablas usuario, planilla, nacionalidades, ocupaciones y departamento
$sql = "
    SELECT 
        u.id_usuario,
        u.nombre,
        u.apellido,
        u.correo_electronico,
        u.numero_telefonico,
        u.fecha_nacimiento,
        u.sexo,
        u.estado_civil,
        n.pais AS nacionalidad,
        o.nombre_ocupacion,
        p.jornada,
        p.hrs,
        p.salario_base,
        p.salario_neto,
        p.codigo_CCSS,
        u.direccion_domicilio,
        p.tipo_quincena,
        d.nombre AS departamento
    FROM usuario u
    JOIN planilla p ON u.id_usuario = p.id_usuario
    JOIN nacionalidades n ON u.id_nacionalidad = n.id_nacionalidad
    LEFT JOIN ocupaciones o ON u.id_ocupacion = o.id_ocupacion
    LEFT JOIN departamento d ON u.id_departamento = d.id_departamento
";

$resultado = $conexion->query($sql);

// Configurar la cabecera para la descarga del archivo Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=reporte_ccss.xls");

// Crear la tabla en formato Excel
echo "<table border='1'>";
echo "<tr>
        <th>Codigo CCSS</th>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>Correo</th>
        <th>Teléfono</th>
        <th>Salario Base</th>
        <th>Salario Neto</th>
        <th>Nacionalidad</th>
        <th>Ocupación</th>
        <th>Departamento</th>
        <th>Tipo de Quincena</th>
    </tr>";

// Recorrer los resultados y generar las filas de la tabla en Excel
while ($fila = $resultado->fetch_assoc()) {
    echo "<tr>
            <td>{$fila['codigo_CCSS']}</td>
            <td>{$fila['nombre']}</td>
            <td>{$fila['apellido']}</td>
            <td>{$fila['correo_electronico']}</td>
            <td>{$fila['numero_telefonico']}</td>
            <td>{$fila['salario_base']}</td>
            <td>{$fila['salario_neto']}</td>
            <td>{$fila['nacionalidad']}</td>
            <td>{$fila['nombre_ocupacion']}</td>
            <td>{$fila['departamento']}</td>
            <td>{$fila['tipo_quincena']}</td>
        </tr>";
}

echo "</table>";

// Cerrar la conexión
$conexion->close();
?>
