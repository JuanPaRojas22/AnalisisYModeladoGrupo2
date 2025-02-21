<?php
interface UsuarioDAO
{

    public function getAllUsers();

    public function getUsersByDepartment($id_departamento); // Nuevo método para filtrar por departamento

    public function getAllDepartments();
    
    public function getUserById($id_usuario);

    public function getVacacionesByUserId($id_usuario);

    public function getHistorialVacacionesByUserId($id_usuario);
    public function getEstadoVacacionById($id_estado_vacacion);

    public function deleteUser($id);

    public function updateUser($nombre, $apellido, $fecha_nacimiento, $fecha_ingreso, $ocupaciones,$nacionalidad, $correo_electronico, $username, $numero_telefonico, $direccion_imagen, $sexo, $estado_civil, $direccion_domicilio, $id_usuario);

    public function AgregarUsuario($id_departamento, $id_rol, $nombre, 
    $apellido, $fecha_nacimiento, $fecha_ingreso, $ocupaciones, $nacionalidad,
    $correo_electronico, $username, $password, $numero_telefonico, 
    $direccion_imagen, $sexo, $estado_civil, $fechacreacion, 
    $usuariocreacion, $fechamodificacion, $usuariomodificacion); 


}


?>