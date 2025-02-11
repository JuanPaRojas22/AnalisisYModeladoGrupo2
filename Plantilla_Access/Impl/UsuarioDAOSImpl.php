<?php
require_once __DIR__ . '/../Interfaces/UsuarioDAO.php';
require_once __DIR__ . '/../Models/usuario.php';

class UsuarioDAOSImpl implements UsuarioDAO
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli("localhost", "root", "", "GestionEmpleados");

    }

    public function getAllUsers()
    {
        $function_conn = $this->conn;
        $users = array();

        // Realiza la consulta SQL
        $stmt = $function_conn->query("SELECT u.*, d.Nombre AS departamento_nombre, r.nombre AS rol_nombre, e.descripcion AS estado_descripcion, e.descripcion as estado
                                        FROM Usuario u
                                        JOIN departamento d ON u.id_departamento = d.id_departamento
                                        JOIN rol r ON u.id_rol = r.id_rol
                                        JOIN estado e ON u.id_estado = e.id_estado
                                        WHERE e.descripcion = 'Activo'");

        // Recorre los resultados y agrega cada fila al array como un array asociativo
        while ($row = $stmt->fetch_assoc()) {
            if (!empty($row['direccion_imagen'])) {
                $row['direccion_imagen'] = 'data:image/jpeg;base64,' . base64_encode($row['direccion_imagen']);
            }
            $users[] = $row;  // Agrega la fila asociativa directamente al array
        }

        // Devuelve el array de usuarios como un array asociativo
        return $users;
    }

 


    public function getDepartmentNameById($id_departamento)
    {
        // Consulta SQL para obtener el nombre del departamento por su ID
        $sql = "SELECT nombre FROM departamento WHERE id_departamento = ?";



        // Prepara la consulta
        $stmt = $this->conn->prepare($sql);

        // Enlaza el parámetro
        $stmt->bind_param("i", $id_departamento); // 'i' es para entero


        // Ejecuta la consulta
        $stmt->execute();

        // Obtiene el resultado
        $result = $stmt->get_result();



        // se verifica si se obtuvo un resultado
        if ($result->num_rows > 0) {
            // Acá se devuelve el nombre del departamento
            $row = $result->fetch_assoc();
            return $row['nombre'];
        } else {
            return 'Desconocido'; // Devuelve un valor por defecto si no se encuentra
        }


    }

    public function getAllDepartments()
    {
        $sql = "SELECT id_departamento, Nombre FROM departamento"; // Asumiendo que la tabla se llama 'departamentos'

        // Prepara la consulta
        $stmt = $this->conn->prepare($sql);

        // Ejecuta la consulta
        $stmt->execute();

        // Obtiene el resultado
        $result = $stmt->get_result();

        // Devuelve los departamentos como un array asociativo
        return $result->fetch_all(MYSQLI_ASSOC);
    }


    // Método para obtener usuarios por departamento
    public function getUsersByDepartment($id_departamento)
    {
        $sql = "SELECT u.*, d.nombre AS departamento_nombre, r.nombre AS rol_nombre , e.descripcion AS estado
        FROM usuario u
        JOIN departamento d ON u.id_departamento = d.id_departamento
        JOIN rol r ON u.id_rol = r.id_rol
        JOIN estado e ON u.id_estado = e.id_estado
        WHERE u.id_departamento = ? AND u.id_estado= 1";

        // Prepara la consulta
        $stmt = $this->conn->prepare($sql);

        // Enlaza el parámetro (i = entero)
        $stmt->bind_param("i", $id_departamento);

        // Ejecuta la consulta
        $stmt->execute();

        // Obtiene el resultado
        $result = $stmt->get_result();

        // Array para almacenar los usuarios
        $users = [];

        // Recorre cada fila y procesa la imagen
        while ($row = $result->fetch_assoc()) {
            if (!empty($row['direccion_imagen'])) {
                $row['direccion_imagen'] = 'data:image/jpeg;base64,' . base64_encode($row['direccion_imagen']);
            }
            $users[] = $row;  // Agrega la fila asociativa al array
        }

        // Devuelve el array de usuarios
        return $users;
    }

    public function getUserById($id_usuario)
{
    $sql = "SELECT u.*, d.nombre AS departamento_nombre, r.nombre AS rol_nombre, e.descripcion AS estado
            FROM usuario u
            JOIN departamento d ON u.id_departamento = d.id_departamento
            JOIN rol r ON u.id_rol = r.id_rol
            JOIN estado e ON u.id_estado = e.id_estado
            WHERE u.id_usuario = ?";

    //consulta
    $stmt = $this->conn->prepare($sql);

    // Enlaza el parámetro (i = entero)
    $stmt->bind_param("i", $id_usuario);

    // Ejecuta la consulta
    $stmt->execute();

    // Obtiene el resultado
    $result = $stmt->get_result();

   // Verifica si se encontró el usuario
   if ($result->num_rows > 0) {
    // Recupera los datos del usuario
    $user = $result->fetch_assoc();

    // Procesa la imagen 
    if (!empty($user['direccion_imagen'])) {
        //Convertir el BLOB en base64
        $user['direccion_imagen'] = 'data:image/jpeg;base64,' . base64_encode($user['direccion_imagen']);
    }

    // Devuelve el usuario
    return $user;
} else {
    // Si no se encuentra el usuario
    return null;
}
}


    public function deleteUser($id)
    {
        $conn = $this->conn;
        $query = "UPDATE Usuario SET id_estado = 2 WHERE id_usuario = ?";  // Actualiza el estado a 2 (inactivo)
        $stmt = $conn->prepare(query: $query);
        $stmt->bind_param("i", $id);  // "i" es para especificar que el id es un integer
        return $stmt->execute();
    }


    public function AgregarUsuario(
        $id_departamento,
        $id_rol,
        $nombre,
        $apellido,
        $fecha_nacimiento,
        $fecha_ingreso,
        $cargo,
        $correo_electronico,
        $username,
        $password,
        $numero_telefonico,
        $direccion_imagen,
        $sexo,
        $estado_civil,
        $fechacreacion,
        $usuariocreacion,
        $fechamodificacion,
        $usuariomodificacion
    ) {
        $function_conn = $this->conn;
        $stmt = $function_conn->prepare("Insert into Usuario 
            (id_departamento, id_rol, nombre, apellido, 
              fecha_nacimiento, fecha_ingreso, cargo, correo_electronico, 
              username, password, numero_telefonico, direccion_imagen, sexo,
              estado_civil, fechacreacion, usuariocreacion, 
              fechamodificacion, usuariomodificacion) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ");
        $stmt->bind_param(
            "iissssssssssssssss",
            $id_departamento,
            $id_rol,
            $nombre,
            $apellido,
            $fecha_nacimiento,
            $fecha_ingreso,
            $cargo,
            $correo_electronico,
            $username,
            $password,
            $numero_telefonico,
            $direccion_imagen,
            $sexo,
            $estado_civil,
            $fechacreacion,
            $usuariocreacion,
            $fechamodificacion,
            $usuariomodificacion
        );
        $stmt->execute();
        echo "Nuevo usuario creado." . "<br>";
    }
}

$UsuarioDAO = new UsuarioDAOSImpl();

// $UsuarioDAO->AgregarUsuario(1, 1, "Juan", "Perez", "1990-01-01", date("Y-m-d"), "Developer", "juan.perez@example.com", "juanperez", "password123", "1234567890", "path/to/image.jpg", "M", "Single", date("Y-m-d H:i:s"), "admin", date("Y-m-d H:i:s"), "admin");

/* $users = $UsuarioDAO->getAllUsers();

 foreach ($users as $user) {
     echo $user->id_usuario . ": " . $user->id_departamento . " - " . $user->id_rol . " - " . $user->nombre . " - " . $user->apellido . " - " . $user->fecha_nacimiento . " - " . $user->fecha_ingreso . " - " . $user->cargo . " - " . $user->correo_electronico . " - " . $user->username . " - " . $user->password . " - " . $user->numero_telefonico . " - " . $user->direccion_imagen . " - " . $user->sexo . " - " . $user->estado_civil . " - " . $user->fechacreacion . " - " . $user->usuariocreacion . " - " . $user->fechamodificacion . " - " . $user->usuariomodificacion . "<br>";
 }*/

?>