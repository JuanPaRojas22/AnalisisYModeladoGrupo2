<?php
interface UsuarioDAO
{
    public function getAllUsers();
    public function AgregarUsuario($id_departamento, $id_rol, $nombre, 
    $apellido, $fecha_nacimiento, $fecha_ingreso, $cargo, 
    $correo_electronico, $username, $password, $numero_telefonico, 
    $direccion_imagen, $sexo, $estado_civil, $fechacreacion, 
    $usuariocreacion, $fechamodificacion, $usuariomodificacion);

}
?>