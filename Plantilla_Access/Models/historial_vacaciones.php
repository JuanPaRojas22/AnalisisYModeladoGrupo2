<?php
class Historial_vacaciones
{
    public $id;
        public $id_historial;
        public $id_usuario;
        public $FechaInicio;
        public $FechaFin;
        public $DiasTomados;
        public $Razon;

        public function __construct
        ($id_historial, $id_usuario, $FechaInicio, $FechaFin, $DiasTomados, $Razon) {
            $this->id_historial = $id_historial;
            $this->id_usuario = $id_usuario;
            $this->FechaInicio = $FechaInicio;
            $this->FechaFin = $FechaFin;
            $this->DiasTomados = $DiasTomados;
            $this->Razon = $Razon;

        }

    
}
?>