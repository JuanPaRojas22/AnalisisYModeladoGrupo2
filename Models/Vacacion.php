<?php
class Vacacion
{
        public $id_vacacion;
        public $id_tipo_vacacion;
        public $razon;
        public $diasTomado;
        public $fecha_inicio;
        public $fecha_fin;

        public $observaciones;
        public $id_departamento;
        public $id_estado;
        public $id_usuario;
        public $id_historial;
        public $fechacreacion;
        public $usuariocreacion;
        public $fechamodificacion;
        public $usuariomodificacion;

        public function __construct
        ($id_vacacion, $id_tipo_vacacion, $razon, $diasTomado, $fecha_inicio, 
        $observaciones, $id_departamento, $id_estado, $id_usuario, $id_historial, 
        $fechacreacion, $usuariocreacion, $fechamodificacion, $usuariomodificacion) {
            $this->id_vacacion = $id_vacacion;
            $this->id_tipo_vacacion = $id_tipo_vacacion;
            $this->razon = $razon;
            $this->diasTomado = $diasTomado;
            $this->fecha_inicio = $fecha_inicio;
            $this->observaciones = $observaciones;
            $this->id_departamento = $id_departamento;
            $this->id_estado = $id_estado;
            $this->id_usuario = $id_usuario;
            $this->id_historial = $id_historial;
            $this->fechacreacion = $fechacreacion;
            $this->usuariocreacion = $usuariocreacion;
            $this->fechamodificacion = $fechamodificacion;
            $this->usuariomodificacion = $usuariomodificacion;
        }

    
}
?>