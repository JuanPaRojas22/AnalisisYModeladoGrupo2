<?php
session_start();
require_once __DIR__ . '/Impl/Historial_Solicitud_Modificacion_VacacionesDAOSImpl.php';
// Verifica si se recicbio el id del usuario y la accion a realizar
if(isset($_GET['id']) && isset($_GET['accion'])){
    $id_historial_solicitud_modificacion = $_GET['id'];
    // $id_usuario = $_GET['id'];
    // Función para obtener el id_vacacion basado en el id_usuario
    // *****************************************************************************************

    // Funcion para obtener el id_usuario basado en el id_historial_solicitud_modificacion
    function obtenerIdUsuarioPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion) {
        $conexion = new mysqli("localhost", "root", "", "GestionEmpleados");
        if ($conexion->connect_error) {
            die("Conexión fallida: " . $conexion->connect_error);
        }
        $sql = "SELECT id_usuario FROM historial_solicitud_modificacion_vacaciones WHERE id_historial_solicitud_modificacion = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $id_historial_solicitud_modificacion);
        $stmt->execute();
        $stmt->bind_result($id_usuario);
        $stmt->fetch();
        $stmt->close();
        $conexion->close();
        return $id_usuario;
    }

    // Funcion para obtener el id_vacacion basado en el id_historial_solicitud_modificacion
    function obtenerIdVacacionPorHistorialSolicitudModificacion($id_historial_solicitud_modificacion) {
        $conexion = new mysqli("localhost", "root", "", "GestionEmpleados");
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
        $conn = new mysqli("localhost", "root", "", "GestionEmpleados");
        
        $sql = "SELECT HSMV.fecha_inicio AS NuevaFechaInicio, HSMV.fecha_fin AS NuevaFechaFin, 
                HSMV.dias_solicitados AS NuevosDiasSolicitados, HSMV.razon_modificacion
                FROM Historial_Solicitud_Modificacion_Vacaciones HSMV
                WHERE HSMV.id_historial_solicitud_modificacion = ?";
        //consulta
        $stmt = $conn->prepare($sql);

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
        $conexion = new mysqli("localhost", "root", "", "GestionEmpleados");
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
    // Verifica si la accion es aprobar o rechazar
    if($accion == 'aprobar'){
        $Historial_Solicitud_ModificacionDAO->
        aprobarSolicitudModificacionVacaciones($id_historial_solicitud_modificacion, $id_vacacion_usuario_solicitado, $id_usuario, $razon_modificacion, $NuevosDiasSolicitados, 
        $NuevaFechaInicio, $Observacion_Administrador_Actual, $Id_historial_usuario_solicitado, $NuevaFechaFin);
    }else if($accion == 'rechazar'){
        $Historial_Solicitud_ModificacionDAO->rechazarSolicitudModificacionVacaciones($id_usuario);
    }
    
    // Se redirije de nuevo a la pagina de detalle de vacaciones
    header('Location: EditarVacaciones.php');
    exit();
} else {
    echo "Parametros incorrectos";
}

?>