<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(["error" => "Usuario no autenticado"]);
    exit;
}

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

// Obtener el ID del usuario autenticado
$id_usuario = $_SESSION['id_usuario'];

// Depuración: Verificar el ID del usuario autenticado
error_log("Usuario autenticado: " . $id_usuario);

// Consulta para obtener TODOS los beneficios del usuario
$sql = "SELECT b.id_beneficio, 
               CONCAT(u.nombre, ' ', u.apellido) AS empleado, 
               b.razon, 
               b.monto, 
               b.identificacion_medismart, 
               b.valor_plan_total, 
               b.aporte_patrono, 
               b.beneficiarios, 
               b.fechacreacion
        FROM beneficios b
        INNER JOIN usuario u ON b.id_usuario = u.id_usuario
        WHERE b.id_usuario = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultado = $stmt->get_result();

$beneficios = [];
while ($row = $resultado->fetch_assoc()) {
    $beneficios[] = $row; // 🔥 Asegura que se agreguen múltiples registros al array
    error_log("Beneficio encontrado: " . json_encode($row)); // Debug
}

// Depuración: Mostrar cuántos beneficios encontró
error_log("Total beneficios encontrados: " . count($beneficios));

// Si no hay beneficios, registrar en logs
if (empty($beneficios)) {
    error_log("No se encontraron beneficios para el usuario con ID: " . $id_usuario);
}

echo json_encode($beneficios, JSON_PRETTY_PRINT); // 🔥 Muestra el JSON de manera legible

$stmt->close();
$conn->close();
?>
