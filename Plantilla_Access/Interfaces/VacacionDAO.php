<?php
interface VacacionDAO
{

    // Funcion que obtiene las solicitudes pendientes de vacaciones de empleados del departamento del administrador actual.
    public function getSolicitudesPendientes($id_departamento);

    // Funcion para aprobar una solicitud de vacaciones
    public function aprobarSolicitud($id_usuario);

    // Funcion para rechazar una solicitud de vacaciones
    public function rechazarSolicitud($id_usuario);

    // Funcion que calcula los dias de vacaciones disponibles de un empleado
    public function calcularDiasDisponibles($id_usuario, $diasTomado, $fecha_inicio, $DiasRestantes);

    // Funcion para comprobar dias feriados y dias habiles
    public function validaFechas($fecha_inicio, $fecha_fin);

    // Funcion verificar que el empleado no exceda su limite anueal de vacaciones
    public function validarDiasDisponibles($id_usuario, $FechaInicio, $FechaFin);

}


?>