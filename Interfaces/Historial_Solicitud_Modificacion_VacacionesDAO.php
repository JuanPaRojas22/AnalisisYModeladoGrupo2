<?php
interface Historial_Solicitud_Modificacion_VacacionesDAO
    {

        // Funcion que ingresa un historial de solicitud de modificacion de vacaciones
        public function IngresarHistorialSolicitudModificacionVacaciones
        ($id_vacacion, $fecha_solicitud, $fecha_resolucion, $fecha_inicio, $fecha_fin, $dias_solicitados, 
        $id_usuario, $usuario_aprobador, $razon_modificacion, $estado);

        // Funcion para obtener el historial de solicitudes de modificacion de vacaciones de un empleado en especifico y su vacacion original solicitada.
        public function getHistorialSolicitudModificacionVacaciones($id_historial_solicitud_modificacion);

        // Funcion para obtener el id_usuario de una vacacion modificada
        public function getUserByIdHistorialSolicitudModificacion($id_historial_solicitud_modificacion);

        // Funcion que obtiene las solicitudes de editar vacaciones aprobadas o pendientes de empleados del departamento del administrador actual. 
        public function getSolicitudesEditarPendientes_O_Aprobadas($id_departamento);
        
        // Funcion que aprueba o rechaza una solicitud de modificacion de vacacione 
        // Cuando apruebbe la solicitud de vacacions, se modifique los dias restantes del empleado y se cambie el estado de la solicitud a aprobada.
        public function aprobarSolicitudModificacionVacaciones
        ($id_historial_solicitud_modificacion, $id_vacacion_usuario_solicitado, $id_usuario, $razon_modificacion, 
        $NuevosDiasSolicitados, $NuevaFechaInicio, $Observacion_Administrador_Actual, $Id_historial_usuario_solicitado, $NuevaFechaFin);

        // Funcion que rechaza una solicitud de modificacion de vacacione 
        // Cuando se rechace la solicitud de vacacions, se cambie el estado de la solicitud a rechazada.
        public function rechazarSolicitudModificacionVacaciones($id_historial_solicitud_modificacion);

        // Funcion para la paginacion de las solicitudes de modificacion de vacaciones por estado
        public function contarHistorialModificadoPorUsuario($id_usuario);

    }


?>