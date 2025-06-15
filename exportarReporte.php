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
