<?php
require_once __DIR__ . '/../Interfaces/Historial_Solicitud_Modificacion_VacacionesDAO.php';
require_once __DIR__ . '/../Models/Historial_Solicitud_Modificacion_Vacaciones.php';

class Historial_Solicitud_Modificacion_VacacionesDAOSImpl implements Historial_Solicitud_Modificacion_VacacionesDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli("localhost", "root", "", "GestionEmpleados");

    }

     // Funcion que ingresa un historial de solicitud de modificacion de vacaciones
     public function IngresarHistorialSolicitudModificacionVacaciones
     ($id_vacacion, $fecha_solicitud, $fecha_resolucion, $fecha_inicio, $fecha_fin, $dias_solicitados, 
     $id_usuario, $usuario_aprobador, $razon_modificacion, $estado){
            $function_conn = $this->conn;
            // Se prepara el comando de insercion
            $stmt = $function_conn->prepare(
                "INSERT INTO historial_solicitud_modificacion_vacaciones 
                (id_vacacion, fecha_solicitud, fecha_resolucion, fecha_inicio, fecha_fin, dias_solicitados, 
                id_usuario, usuario_aprobador, razon_modificacion, estado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            // Se enlazan los parametros 
            $stmt->bind_param(
                "issssiisss",
                $id_vacacion,
                $fecha_solicitud,
                $fecha_resolucion,
                $fecha_inicio,
                $fecha_fin,
                $dias_solicitados,
                $id_usuario,
                $usuario_aprobador,
                $razon_modificacion,
                $estado
            );

            // Se ejecuta el comando
            $stmt->execute();
            $stmt->close();
     }

    // Funcion para obtener el historial de solicitudes de modificacion de vacaciones de un empleado en especifico y su vacacion original solicitada.
    public function getHistorialSolicitudModificacionVacaciones($id_historial_solicitud_modificacion)
    {
        $sql = "SELECT HSMV.id_historial_solicitud_modificacion, HSMV.id_usuario, U.Nombre, U.Apellido, Dep.Nombre AS Departamento, -- Informacion del usuario
                -- Se muestra la NUEVA fecha de inicio, nueva fecha de fin y nuevos dias solicitados
                HSMV.fecha_inicio AS NuevaFechaInicio, HSMV.fecha_fin AS NuevaFechaFin, HSMV.dias_solicitados AS NuevosDiasSolicitados, HSMV.razon_modificacion,
                -- Se muestra la ORIGINAL fecha de inicio, fecha de fin solicitada y dias solicitados
                V.fecha_inicio AS OriginalFechaInicio, V.fecha_fin AS OriginalFechaFin, V.diasTomado AS OriginalDiasSolicitados,
                -- Se muestra los dias restantes de vacaciones que le queda al usuario. 
                HV.DiasRestantes, 
                -- Se muestra el estado de la solicitud de modificacion del usuario. 
                HSMV.estado AS EstadoSolicitudVacacion
                FROM Historial_Solicitud_Modificacion_Vacaciones HSMV
                -- Uno la tabla de usuario e Historial_Solicitud_Modificacion_Vacaciones con el id_usuario
                INNER JOIN usuario U ON HSMV.id_usuario = U.id_usuario
                -- Uno la tabla de departamento e usuario con el id_departamento del usuario
                INNER JOIN departamento Dep ON U.id_departamento = Dep.id_departamento
                -- Uno la tabla de vacacion e Historial_Solicitud_Modificacion_Vacaciones con el id_vacacion
                INNER JOIN vacacion V ON HSMV.id_vacacion = V.id_vacacion
                -- Uno la tabla de estado_vacacion e vacacion con el id_estado_vacacion
                INNER JOIN historial_vacaciones HV ON V.id_historial = HV.id_historial
                WHERE HSMV.estado = 'Pendiente' AND HSMV.id_historial_solicitud_modificacion = ?
                ORDER BY U.Nombre ASC ";
        //consulta
        $stmt = $this->conn->prepare($sql);

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

    // Funcion para obtener el id_usuario de una vacacion modificada
    public function getUserByIdHistorialSolicitudModificacion($id_historial_solicitud_modificacion){
        $sql = "SELECT id_usuario FROM historial_solicitud_modificacion_vacaciones WHERE id_historial_solicitud_modificacion = ?";

        // Prepara la consulta
        $stmt = $this->conn->prepare($sql);

        // Enlaza el parámetro (i = entero)
        $stmt->bind_param("i", $id_historial_solicitud_modificacion);

        // Ejecuta la consulta
        $stmt->execute();

        // Obtiene el resultado
        $result = $stmt->get_result();

        // Obtiene el id_usuario
        $id_usuario = null;
        if ($row = $result->fetch_assoc()) {
            $id_usuario = $row['id_usuario'];
        }

        // Devuelve el id_usuario
        return $id_usuario;

    }

    // Funcion que obtiene las solicitudes de editar vacaciones aprobadas o pendientes de empleados del departamento del administrador actual. 
    public function getSolicitudesEditarPendientes_O_Aprobadas($id_departamento){
        $function_conn = $this->conn;
        // Se obtienen las solicitudes de editar vacaciones aprobadas o pendientes de empleados del departamento del administrador actual. 
        $stmt = $function_conn->prepare(
            "SELECT HSMV.id_historial_solicitud_modificacion, HSMV.id_usuario, U.Nombre, U.Apellido, Dep.Nombre AS Departamento, 
            HSMV.fecha_inicio AS NuevaFechaInicio, HSMV.fecha_fin AS NuevaFechaFin, HSMV.dias_solicitados, HV.DiasRestantes, HSMV.estado
            FROM Historial_Solicitud_Modificacion_Vacaciones HSMV
            INNER JOIN usuario U ON HSMV.id_usuario = U.id_usuario
            INNER JOIN departamento Dep ON U.id_departamento = Dep.id_departamento
            INNER JOIN vacacion V ON HSMV.id_vacacion = V.id_vacacion
            INNER JOIN estado_vacacion EH ON V.id_estado_vacacion = EH.id_estado_vacacion
            INNER JOIN historial_vacaciones HV ON V.id_historial = HV.id_historial
            WHERE HSMV.estado = 'Pendiente'
            AND U.id_departamento = ?
            ORDER BY U.Nombre ASC");
        
        // Se ejecuta el comando
        $stmt->bind_param("i", $id_departamento);
        $stmt->execute();

        return $stmt->get_result(); // Se retorna el resultado de la consulta
    }

    // Funcion que aprueba una solicitud de modificacion de vacacione 
    // Cuando se apruebbe la solicitud de vacacions, se modifique los dias restantes del empleado, 
    // se cree un nuevo registro en la tabla vacacion con los nuevos datos (Y que se cambie a "VacacionModificada" ) , 
    // la solicitud original se cambie a tipo "VacacionOriginalModificada" y se cambie el estado de la solicitud modificada a aprobada.
    public function aprobarSolicitudModificacionVacaciones
    ($id_historial_solicitud_modificacion, $id_vacacion_usuario_solicitado, $id_usuario, $razon_modificacion, $NuevosDiasSolicitados, $NuevaFechaInicio, $Observacion_Administrador_Actual, $Id_historial_usuario_solicitado, $NuevaFechaFin){
        $function_conn = $this->conn;
        // 1. Se aprueba la solicitud de modificacion de vacaciones
        $stmt = $function_conn->prepare(
            "UPDATE historial_solicitud_modificacion_vacaciones
            SET estado = 'Aprobado' 
            WHERE id_historial_solicitud_modificacion = ?");

        // 2. Se modifica la vacacion original para modificar el estado a VacacionOriginalModificada
        $stmt2 = $function_conn->prepare(
            "UPDATE vacacion
            SET id_estado_vacacion = 5 -- Se cambia a tipo VacacionOriginalModificada
            WHERE id_vacacion = ?");

        // 3. Se ingresa una nueva vacacion que sirve como registro de la vacacion modificada
        $stmt3 = $function_conn->prepare(
                "INSERT INTO vacacion (razon, diasTomado, 
                fecha_inicio, observaciones, id_usuario, id_historial, fechacreacion, 
                usuariocreacion, fechamodificacion, usuariomodificacion, id_estado_vacacion, SolicitudEditar, fecha_fin
                ) VALUES (
                ?,     -- Se ingresa la nueva razon de modificacion
                ?,  -- Se ingresan los nuevos dias solicitados
                ?,       -- Se ingresa la nueva fecha de inicio
                ?, -- Se ingresan las observaciones brindadas por el administrador
                ?,             -- Se ingresa el usuario que solicito la modificacion
                ?, -- Se ingresa el historial del usuario solicitado
                CURDATE(),               -- fechacreacion (fecha actual)
                'admin',                 -- usuariocreacion
                NULL,                    -- fechamodificacion
                NULL,                    -- usuariomodificacion
                4,                       -- Se cambia a tipo VacacionModificada
                'No',                    -- SolicitudEditar
                ?             -- fecha_fin
                );
                ");


        // 4. Se actualiza el historial de vacaciones para modificar los dias restantes del usuario
        $stmt4 = $function_conn->prepare(
            "UPDATE historial_vacaciones
            SET DiasRestantes = DiasRestantes - ?
            WHERE id_historial = ?");

        // Se ejecuta el primer comando
        $stmt->bind_param("i", $id_historial_solicitud_modificacion);
        $stmt->execute();
        $stmt->close();

        // Se ejecuta el segundo comando
        $stmt2->bind_param("i", $id_vacacion_usuario_solicitado);
        $stmt2->execute();
        $stmt2->close();

        // Se ejecuta el tercer comando
        $stmt3->bind_param("sissiis", 
        $razon_modificacion, 
        $NuevosDiasSolicitados, 
        $NuevaFechaInicio, 
        $Observacion_Administrador_Actual, 
        $id_usuario, 
        $Id_historial_usuario_solicitado, 
        $NuevaFechaFin);
        $stmt3->execute();
        $stmt3->close();

        // Se ejecuta el cuarto comando
        $stmt4->bind_param("ii", $NuevosDiasSolicitados, $Id_historial_usuario_solicitado);
        $stmt4->execute();
        $stmt4->close();

        echo "Solicitud aprobada" . "<br>";
    }

    // Funcion que rechaza una solicitud de modificacion de vacaciones
    public function rechazarSolicitudModificacionVacaciones($id_historial_solicitud_modificacion){
        $function_conn = $this->conn;
        $stmt = $function_conn->prepare(
            "UPDATE historial_solicitud_modificacion_vacaciones
            SET estado = 'Rechazado'
            WHERE id_historial_solicitud_modificacion = ?");
        $stmt->bind_param(
            "i",
            $id_historial_solicitud_modificacion
        );
        $stmt->execute();
        echo "Solicitud rechazada" . "<br>";
        $stmt->close();
    }

}

?>