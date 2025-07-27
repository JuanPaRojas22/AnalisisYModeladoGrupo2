<?php
session_start();
require_once __DIR__ . '/Impl/VacacionDAOSImpl.php';
require_once __DIR__ . '/notificaciones_util.php'; // Para insertar notificación

// Verifica si se recicbio el id del usuario y la accion a realizar
if(isset($_GET['id']) && isset($_GET['accion'])){
    $id_vacacion = $_GET['id'];
    $accion = $_GET['accion'];
    $VacacionDAO = new VacacionDAOSImpl();

    // Logica para mandar el mensaje por correo

    // Verifica si la accion es aprobar o rechazar
    if($accion == 'aprobar'){
        // Conectar a la base de datos
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

        $query = "SELECT diasTomado, id_usuario FROM vacacion WHERE id_vacacion = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_vacacion);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        $diasTomado = $row['diasTomado'];
        $id_usuario = $row['id_usuario'];

        $stmt->close();
        $conn->close();

        $VacacionDAO->aprobarSolicitud($id_vacacion, $diasTomado, $id_usuario);
        insertarNotificacion($id_usuario, "✅ Tu solicitud de vacaciones fue aprobada.");

    }else if($accion == 'rechazar'){
        

        $VacacionDAO->rechazarSolicitud($id_vacacion);
        // Obtener ID del usuario también si no lo has guardado antes
$id_usuario = $_SESSION['id_usuario'] ?? null;
if ($id_usuario) {
    insertarNotificacion($id_usuario, "❌ Tu solicitud de vacaciones fue rechazada.");
}

    }
    
    // Se redirije de nuevo a la pagina de detalle de vacaciones
    header('Location: vacaciones.php?id='.$id_vacacion);
    exit();
} else {
    echo "Parametros incorrectos";
}

?>
