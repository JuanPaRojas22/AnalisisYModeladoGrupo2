<?php
class Historial_Solicitud_Modificacion_Vacaciones
{
    public $id_historial_solicitud_modificacion;
    public $id_vacacion;
    public $fecha_solicitud;
    public $fecha_resolucion;
    public $fecha_inicio;
    public $fecha_fin;
    public $dias_solicitados;
    public $id_usuario;
    public $usuario_aprobador;
    public $razon_modificacion;
    public $estado;


    public function __construct
        ($id_historial_solicitud_modificacion, $id_vacacion, $fecha_solicitud, $fecha_resolucion, $fecha_inicio, 
        $fecha_fin, $dias_solicitados, $id_usuario, $usuario_aprobador, $razon_modificacion, $estado) {
            $this->id_historial_solicitud_modificacion = $id_historial_solicitud_modificacion;
            $this->id_vacacion = $id_vacacion;
            $this->fecha_solicitud = $fecha_solicitud;
            $this->fecha_resolucion = $fecha_resolucion;
            $this->fecha_inicio = $fecha_inicio;
            $this->fecha_fin = $fecha_fin;
            $this->dias_solicitados = $dias_solicitados;
            $this->id_usuario = $id_usuario;
            $this->usuario_aprobador = $usuario_aprobador;
            $this->razon_modificacion = $razon_modificacion;
            $this->estado = $estado;

        }

    
}
?>