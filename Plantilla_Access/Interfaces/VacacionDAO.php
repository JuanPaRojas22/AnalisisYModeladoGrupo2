<?php
interface VacacionDAO
{

    // Funcion para obtener el usuario de la vacacion actual
    public function getUserByIdVacacion($id_vacacion);

    // Funcion para obtener el detalle de la vacacion actual
    public function getDetalleVacacion($id_vacacion);

    // // Funcion que obtiene las vacaciones solicitadas por el usuario actual. Se obtienen todas las vacaciones que esten en estado pendiente
    public function getVacacionesSolicitadas($id_usuario);

    // Funcion que obtiene las solicitudes pendientes de vacaciones de empleados del departamento del administrador actual.
    public function getSolicitudesPendientes($id_departamento);

    // Funcion para aprobar una solicitud de vacaciones
    public function aprobarSolicitud($id_vacacion);

    // Funcion para rechazar una solicitud de vacaciones
    public function rechazarSolicitud($id_vacacion);

    // Funcion que calcula los dias de vacaciones disponibles de un empleado
    public function calcularDiasDisponibles($id_usuario, $diasTomado, $fecha_inicio, $DiasRestantes);

    // Funcion para comprobar dias feriados y dias habiles
    public function validaFechasFeriados($fecha_inicio, $fecha_fin);

    // Funcion para verificar que el empleado no exceda su limite anual de vacaciones
    public function validarDiasDisponibles($id_usuario, $diasTomado);

    // Funcion para ingresar una solicitud de vacaciones
    public function IngresarVacacion($razon, $diasTomado, $FechaInicio, $observaciones, $id_usuario, $id_historial, $fechacreacion, 
                                     $usuariocreacion, $fechamodificacion, $usuariomodificacion, $id_estado_vacacion, $SolicitudEditar, $fecha_fin);

    

}


?>