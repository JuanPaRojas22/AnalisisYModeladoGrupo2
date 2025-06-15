<?php
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

$id_usuario = $_GET['id_usuario'];

// Traer ocupación y salario base
$sql = "SELECT u.id_ocupacion, p.salario_base 
        FROM Usuario u
        LEFT JOIN Planilla p ON u.id_usuario = p.id_usuario 
        WHERE u.id_usuario = '$id_usuario' 
        LIMIT 1";

$resultado = $conn->query($sql);

$data = [];

if ($resultado->num_rows > 0) {
    $fila = $resultado->fetch_assoc();
    $data = [
        'puesto_anterior' => $fila['id_ocupacion'],
        'sueldo_anterior' => $fila['salario_base']
    ];
}

echo json_encode($data);
?>
