<?php
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
session_start();
// Configurar encabezados para la descarga
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=reporte_antiguedad.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Consulta para obtener los datos del reporte
$query = "
    SELECT 
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

// Imprimir encabezados de las columnas
echo "Nombre Completo\tFecha de Ingreso\tAntigüedad (Años)\tAntigüedad (Meses)\tBeneficio\tMonto del Beneficio\tFecha del Beneficio\n";

// Imprimir los datos
while ($row = $result->fetch_assoc()) {
    echo "{$row['nombre_completo']}\t{$row['fecha_ingreso']}\t{$row['antiguedad_anios']}\t{$row['antiguedad_meses']}\t{$row['beneficio']}\t{$row['monto_beneficio']}\t{$row['fecha_beneficio']}\n";
}
?>
