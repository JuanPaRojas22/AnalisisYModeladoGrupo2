<?php
interface Historial_Solicitud_Modificacion_VacacionesDAO
    {

        // Funcion para obtener el historial de solicitudes de modificacion de vacaciones de un empleado en especifico y su vacacion original solicitada.
        public function getHistorialSolicitudModificacionVacaciones($id_usuario);

        // Funcion que obtiene las solicitudes de editar vacaciones aprobadas o pendientes de empleados del departamento del administrador actual. 
        public function getSolicitudesEditarPendientes_O_Aprobadas($id_departamento);
        
        // Funcion que aprueba o rechaza una solicitud de modificacion de vacacione 
        // Cuando apruebbe la solicitud de vacacions, se modifique los dias restantes del empleado y se cambie el estado de la solicitud a aprobada.
        public function aprobarSolicitudModificacionVacaciones
        ($id_usuario, $id_vacacion_usuario_solicitado, $id_tipo_vacacion_usuario_solicitado, $razon_modificacion, $NuevosDiasSolicitados, $NuevaFechaInicio, $Observacion_Administrador_Actual, $Id_historial_usuario_solicitado, $NuevaFechaFin);

        // Funcion que rechaza una solicitud de modificacion de vacacione 
        // Cuando se rechace la solicitud de vacacions, se cambie el estado de la solicitud a rechazada.
        public function rechazarSolicitudModificacionVacaciones($id_usuario);

    }


?>