<?php
class Usuario
{
    public $id;
        public $id_usuario;
        public $id_departamento;
        public $id_rol;
        public $nombre;
        public $apellido;
        public $fecha_nacimiento;
        public $fecha_ingreso;
        public $cargo;
        public $correo_electronico;
        public $username;
        public $password;
        public $numero_telefonico;
        public $direccion_imagen;
        public $sexo;
        public $estado_civil;
        public $fechacreacion;
        public $usuariocreacion;
        public $fechamodificacion;
        public $usuariomodificacion;


        public function __construct
        ($id_usuario, $id_departamento, $id_rol, $nombre, 
        $apellido, $fecha_nacimiento, $fecha_ingreso, $cargo, 
        $correo_electronico, $username, $password, $numero_telefonico, 
        $direccion_imagen, $sexo, $estado_civil, $fechacreacion, 
        $usuariocreacion, $fechamodificacion, $usuariomodificacion) {
            $this->id_usuario = $id_usuario;
            $this->id_departamento = $id_departamento;
            $this->id_rol = $id_rol;
            $this->nombre = $nombre;
            $this->apellido = $apellido;
            $this->fecha_nacimiento = $fecha_nacimiento;
            $this->cargo = $cargo;
            $this->correo_electronico = $correo_electronico;
            $this->username = $username;
            $this->password = $password;
            $this->numero_telefonico = $numero_telefonico;
            $this->direccion_imagen = $direccion_imagen;
            $this->sexo = $sexo;
            $this->estado_civil = $estado_civil;
            $this->fechacreacion = $fechacreacion;
            $this->usuariocreacion = $usuariocreacion;
            $this->fechamodificacion = $fechamodificacion;
            $this->usuariomodificacion = $usuariomodificacion;

        }

    
}
?>