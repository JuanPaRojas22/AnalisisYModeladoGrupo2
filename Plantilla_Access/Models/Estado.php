<?php
class Estado
{
        public $id_estado;
        public $descripcion;

        public function __construct
        ($id_estado, $descripcion) {
            $this->id_estado = $id_estado;
            $this->descripcion = $descripcion;
        }

    
}
?>