<?php
session_start();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/Impl/Historial_Solicitud_Modificacion_VacacionesDAOSImpl.php';
require_once __DIR__ . '/notificaciones_util.php';

if (isset($_GET['id']) && isset($_GET['accion'])) {
    $id_historial_solicitud_modificacion = $_GET['id'];
    $accion = $_GET['accion'];
    error_log("🚨 Acción recibida: $accion para solicitud: $id_historial_solicitud_modificacion");

    function obtenerIdUsuarioPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion) {
        error_log("🔎 Obteniendo ID de usuario para historial: $id_historial_solicitud_modificacion");

        $host = "accespersoneldb.mysql.database.azure.com";
        $user = "adminUser";
        $password = "admin123+";
        $dbname = "gestionEmpleados";
        $port = 3306;
        $ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

        $conn = mysqli_init();
        mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
        mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

        if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
            error_log("❌ Error de conexión");
            die("Error de conexión: " . mysqli_connect_error());
        }

        mysqli_set_charset($conn, "utf8mb4");

        $sql = "SELECT id_usuario FROM historial_solicitud_modificacion_vacaciones WHERE id_historial_solicitud_modificacion = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_historial_solicitud_modificacion);
        $stmt->execute();
        $stmt->bind_result($id_usuario);
        $stmt->fetch();
        $stmt->close();
        $conn->close();

        error_log("✅ ID usuario obtenido: $id_usuario");
        return $id_usuario;
    }

    function obtenerIdVacacionPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion) {
        $conexion = obtenerConexion();
        $sql = "SELECT id_vacacion FROM historial_solicitud_modificacion_vacaciones WHERE id_historial_solicitud_modificacion = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_historial_solicitud_modificacion);
        $stmt->execute();
        $stmt->bind_result($id_vacacion);
        $stmt->fetch();
        $stmt->close();
        $conexion->close();
        error_log("📌 ID vacación obtenida: $id_vacacion");
        return $id_vacacion;
    }

    function obtenerDatosNuevosPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion) {
        $conexion = obtenerConexion();
        $sql = "SELECT fecha_inicio AS NuevaFechaInicio, fecha_fin AS NuevaFechaFin, dias_solicitados AS NuevosDiasSolicitados, razon_modificacion FROM Historial_Solicitud_Modificacion_Vacaciones WHERE id_historial_solicitud_modificacion = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_historial_solicitud_modificacion);
        $stmt->execute();
        $result = $stmt->get_result();
        $datos = ($result->num_rows > 0) ? $result->fetch_assoc() : null;
        error_log("📅 Datos de la solicitud obtenidos: " . json_encode($datos));
        return $datos;
    }

    function obtenerIdHistorialPorUsuario($id_usuario) {
        $conexion = obtenerConexion();
        $sql = "SELECT id_historial FROM vacacion WHERE id_usuario = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $stmt->bind_result($id_historial);
        $stmt->fetch();
        $stmt->close();
        $conexion->close();
        error_log("📘 ID historial del usuario: $id_historial");
        return $id_historial;
    }

    // Obtener datos clave
    $id_usuario = obtenerIdUsuarioPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion);
    $id_vacacion_usuario = obtenerIdVacacionPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion);
    $datos = obtenerDatosNuevosPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion);
    $id_historial_usuario = obtenerIdHistorialPorUsuario($id_usuario);

    if (!$datos) {
        error_log("⚠️ No se encontraron datos nuevos para el historial ID $id_historial_solicitud_modificacion");
        die("Error al obtener datos de modificación");
    }

    $razon_modificacion = htmlspecialchars($datos['razon_modificacion']);
    $nuevosDias = htmlspecialchars($datos['NuevosDiasSolicitados']);
    $nuevaInicio = htmlspecialchars($datos['NuevaFechaInicio']);
    $nuevaFin = htmlspecialchars($datos['NuevaFechaFin']);
    $observacion = "Solicitud aceptada";

    $DAO = new Historial_Solicitud_Modificacion_VacacionesDAOSImpl();

    if ($accion === 'aprobar') {
        error_log("🟢 Aprobando solicitud...");

        $DAO->aprobarSolicitudModificacionVacaciones(
            $id_historial_solicitud_modificacion,
            $id_vacacion_usuario,
            $id_usuario,
            $razon_modificacion,
            $nuevosDias,
            $nuevaInicio,
            $observacion,
            $id_historial_usuario,
            $nuevaFin
        );

        if ($id_usuario && is_numeric($id_usuario)) {
            insertarNotificacion($id_usuario, "✅ Tu solicitud de modificación de vacaciones fue aprobada. 🎉");
            error_log("📨 Notificación enviada (APROBADA) al usuario $id_usuario");
        } else {
            error_log("⚠️ ID de usuario inválido al insertar notificación (APROBADA)");
        }

    } elseif ($accion === 'rechazar') {
        error_log("🔴 Rechazando solicitud...");

        $DAO->rechazarSolicitudModificacionVacaciones($id_historial_solicitud_modificacion);

        if ($id_usuario && is_numeric($id_usuario)) {
            insertarNotificacion($id_usuario, "❌ Tu solicitud de modificación de vacaciones fue rechazada. Consultá con tu supervisor. 📞");
            error_log("📨 Notificación enviada (RECHAZADA) al usuario $id_usuario");
        } else {
            error_log("⚠️ ID de usuario inválido al insertar notificación (RECHAZADA)");
        }
    }

    header('Location: EditarVacaciones.php');
    exit();

} else {
    error_log("⛔ Parámetros GET faltantes o inválidos");
    echo "Parámetros incorrectos";
}
