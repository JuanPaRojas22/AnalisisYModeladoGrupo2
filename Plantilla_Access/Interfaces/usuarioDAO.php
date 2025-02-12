<?php
interface UsuarioDAO
{

    public function getAllUsers();

    public function getUsersByDepartment($id_departamento); // Nuevo método para filtrar por departamento

    public function getAllDepartments();
    
    public function getUserById($id_usuario);

    public function deleteUser($id);

    public function updateUser($nombre, $apellido, $fecha_nacimiento, $fecha_ingreso, $cargo, $correo_electronico, $username, $numero_telefonico, $direccion_imagen, $sexo, $estado_civil, $direccion_domicilio, $id_usuario);

    public function AgregarUsuario($id_departamento, $id_rol, $nombre, 
    $apellido, $fecha_nacimiento, $fecha_ingreso, $cargo, 
    $correo_electronico, $username, $password, $numero_telefonico, 
    $direccion_imagen, $sexo, $estado_civil, $fechacreacion, 
    $usuariocreacion, $fechamodificacion, $usuariomodificacion); 


}


?>