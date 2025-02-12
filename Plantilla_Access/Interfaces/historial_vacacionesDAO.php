<?php
interface Historial_vacacionesDAO
{

    // Funcion para ingresar una vacacion. 
    public function IngresarVacacion($id_usuario, $FechaInicio, $FechaFin, $DiasTomados, $Razon);

    // Funcion para validar si el empleado puede obtener la vacacion o no
    public function ValidarVacaciones($id_usuario, $FechaInicio, $FechaFin, $fecha_ingreso, $DiasTomados );

}


?>