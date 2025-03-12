<?php
interface Historial_vacacionesDAO
{

    // Funcion para devolver la cantidad de dias ya utilizados por el empleado
    public function IngresarVacacion($id_usuario, $FechaInicio, $FechaFin, $DiasTomados, $Razon);

    // Funcion para validar si el empleado puede obtener la vacacion o no
    public function ValidarVacaciones($id_usuario, $FechaInicio, $FechaFin, $fecha_ingreso, $DiasTomados );

    // Funcion que devuelve el historial de vacaciones de un empleado en especifico
    public function getHistorialVacaciones($id_usuario);

}


?>