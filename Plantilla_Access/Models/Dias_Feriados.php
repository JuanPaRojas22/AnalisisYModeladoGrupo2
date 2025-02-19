<?php
class Dias_Feriados
{
        public $id_fecha;
        public $nombre_feriado;
        public $fecha;
        public $tipo_feriado;
        public $id_tipo_vacacion;
        public $id_vacacion;

        public function __construct
        ($id_fecha, $nombre_feriado, $fecha, $tipo_feriado, $id_tipo_vacacion, $id_vacacion) {
            $this->id_fecha = $id_fecha;
            $this->nombre_feriado = $nombre_feriado;
            $this->fecha = $fecha;
            $this->tipo_feriado = $tipo_feriado;
            $this->id_tipo_vacacion = $id_tipo_vacacion;
            $this->id_vacacion = $id_vacacion;
        }

    
}
?>