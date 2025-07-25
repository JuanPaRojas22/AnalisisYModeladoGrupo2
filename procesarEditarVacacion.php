<?php
session_start();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/Impl/Historial_Solicitud_Modificacion_VacacionesDAOSImpl.php';
require_once __DIR__ . '/mailer.php'; //  PHPMailer para enviar correos

// Verifica si se recicbio el id del usuario y la accion a realizar
if(isset($_GET['id']) && isset($_GET['accion'])){
    $id_historial_solicitud_modificacion = $_GET['id'];
    // $id_usuario = $_GET['id'];
    // Función para obtener el id_vacacion basado en el id_usuario
    // *****************************************************************************************

    // Funcion para obtener el id_usuario basado en el id_historial_solicitud_modificacion
    function obtenerIdUsuarioPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion) {
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
        
        $sql = "SELECT id_usuario FROM historial_solicitud_modificacion_vacaciones WHERE id_historial_solicitud_modificacion = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_historial_solicitud_modificacion);
        $stmt->execute();
        $stmt->bind_result($id_usuario);
        $stmt->fetch();
        $stmt->close();
        $conn->close();
        return $id_usuario;
    }
    // Función para obtener el correo del usuario basado en su ID
    function obtenerCorreoUsuario($id_usuario) {
        $conexion = obtenerConexion();
        if ($conexion->connect_error) {
            die("Conexión fallida: " . $conexion->connect_error);
        }
        $sql = "SELECT correo_electronico FROM usuario WHERE id_usuario = ?";

        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->bind_result($correo);
        $stmt->fetch();
        $stmt->close();
        $conexion->close();
        return $correo;
    }


    // Funcion para obtener el id_vacacion basado en el id_historial_solicitud_modificacion
    function obtenerIdVacacionPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion) {
        $conexion = obtenerConexion();
        if ($conexion->connect_error) {
            die("Conexión fallida: " . $conexion->connect_error);
        }
        $sql = "SELECT id_vacacion FROM historial_solicitud_modificacion_vacaciones WHERE id_historial_solicitud_modificacion = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_historial_solicitud_modificacion);
        $stmt->execute();
        $stmt->bind_result($id_vacacion);
        $stmt->fetch();
        $stmt->close();
        $conexion->close();
        return $id_vacacion;
    }

    // Función para obtener nuevos datos basado en el id_historial_solicitud_modificacion
    function obtenerDatosNuevosPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion) {
        $conexion = obtenerConexion();
        
        $sql = "SELECT HSMV.fecha_inicio AS NuevaFechaInicio, HSMV.fecha_fin AS NuevaFechaFin, 
                HSMV.dias_solicitados AS NuevosDiasSolicitados, HSMV.razon_modificacion
                FROM Historial_Solicitud_Modificacion_Vacaciones HSMV
                WHERE HSMV.id_historial_solicitud_modificacion = ?";
        //consulta
        $stmt = $conexion->prepare($sql);

        // Enlaza el parámetro (i = entero)
        $stmt->bind_param("i", $id_historial_solicitud_modificacion);

        // Ejecuta la consulta
        $stmt->execute();

        // Obtiene el resultado
        $result = $stmt->get_result();

        // Verifica si se encontró el usuario
        if ($result->num_rows > 0) {
            // Recupera los datos del usuario
            $user = $result->fetch_assoc();

            // Devuelve el usuario
            return $user;
        } else {
            // Si no se encuentra el usuario
            return null;
        }
    
    }

    // Obtener el id_usuario en base al id_historial_solicitud_modificacion
    $id_usuario = obtenerIdUsuarioPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion);

     // obtener el correo de usuario
     $correo_usuario = obtenerCorreoUsuario($id_usuario); // Obtener el correo del usuario


    // Obtener el atributo id_vacacion en base al id_historial_solicitud_modificacion del usuario solicitado
    $id_vacacion_usuario_solicitado = obtenerIdVacacionPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion);

    // Obtiene los nuevos datos de la solicitud de modificacion de vacaciones
    $Historial_Solicitud_Modificacion_Vacaciones = obtenerDatosNuevosPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion);


    // Obtiene la razon de modificacion de vacaciones
    $razon_modificacion = htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['razon_modificacion']);
    
    // Obtiene los nuevos dias solicitados
    $NuevosDiasSolicitados = htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['NuevosDiasSolicitados']);;
    // Obtiene la nueva fecha de inicio
    $NuevaFechaInicio = htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['NuevaFechaInicio']);
    // Obtiene la nueva fecha de fin
    $NuevaFechaFin = htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['NuevaFechaFin']);
    // Obtiene la observacion del administrador actual
    // SE TIENE QUEE CAMBIAR PARA QUE EN UN PHP EL ADMINISTRADOR PUEDA REGISTRAR
    $Observacion_Administrador_Actual = "Solicitud aceptada";

    // Función para obtener el id_vacacion basado en el id_usuario
    function obtenerIdHistorialPorUsuario($id_usuario) {
        $conexion = obtenerConexion();
        if ($conexion->connect_error) {
            die("Conexión fallida: " . $conexion->connect_error);
        }
        $sql = "SELECT id_historial FROM vacacion WHERE id_usuario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->bind_result($id_vacacion);
        $stmt->fetch();
        $stmt->close();
        $conexion->close();
        return $id_vacacion;
    }

    // Obtiene el id del historial del usuario solicitado
    $Id_historial_usuario_solicitado = obtenerIdHistorialPorUsuario($id_usuario);

    

$accion = $_GET['accion'];
$Historial_Solicitud_ModificacionDAO = new Historial_Solicitud_Modificacion_VacacionesDAOSImpl();

// 📌 Obtener el ID del usuario
$id_usuario = obtenerIdUsuarioPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion);

// 📌 Obtener el correo real del usuario desde la base de datos
$correo_usuario = obtenerCorreoUsuario($id_usuario);  

// 📌 Estilos CSS separados
$css = "
    <style>
        .container {
            width: 80%;
            margin: auto;
            padding: 20px;
            font-family: Arial, sans-serif;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            text-align: center;
        }
        .header {
            color: white;
            padding: 15px;
            font-size: 22px;
            font-weight: bold;
            border-radius: 8px 8px 0 0;
        }
        .content {
            margin: 20px;
            font-size: 18px;
            color: #333;
        }
        .footer {
            font-size: 14px;
            color: #777;
            margin-top: 20px;
        }
    </style>
";

if ($accion == 'aprobar') {
    $Historial_Solicitud_ModificacionDAO->aprobarSolicitudModificacionVacaciones(
        $id_historial_solicitud_modificacion, $id_vacacion_usuario_solicitado, 
        $id_usuario, $razon_modificacion, $NuevosDiasSolicitados, 
        $NuevaFechaInicio, $Observacion_Administrador_Actual, 
        $Id_historial_usuario_solicitado, $NuevaFechaFin
    );

    // 📌 Mensaje con HTML para la aprobación
$asunto = "✅ Solicitud de Vacaciones Aprobada";
$mensaje = "
    <p>🎉 Hola, tu solicitud de modificación de vacaciones ha sido <strong>aprobada</strong>.</p>
    <p>Disfrutá tu descanso. 🌴</p>
    <p style='font-size:12px;color:#555;'>Este es un mensaje automático del sistema de vacaciones.</p>
";

    enviarCorreo($correo_usuario, $asunto, $mensaje);

} else if ($accion == 'rechazar') {
    $Historial_Solicitud_ModificacionDAO->rechazarSolicitudModificacionVacaciones($id_historial_solicitud_modificacion);


    // 📌 Mensaje con HTML para el rechazo
    $asunto = "❌ Solicitud de Vacaciones Rechazada";
$mensaje = "
    <p>⚠️ Hola, lamentamos informarte que tu solicitud de modificación de vacaciones ha sido <strong>rechazada</strong>.</p>
    <p>Si tenés dudas, consultá con tu supervisor. 📞</p>
    <p style='font-size:12px;color:#555;'>Este es un mensaje automático del sistema de vacaciones.</p>
";


    enviarCorreo($correo_usuario, $asunto, $mensaje);
}

    
    // Se redirije de nuevo a la pagina de detalle de vacaciones
    header('Location: EditarVacaciones.php');
    exit();
} else {
    echo "Parametros incorrectos";
}

?>
