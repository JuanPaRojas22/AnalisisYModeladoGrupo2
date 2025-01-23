<?php
    require_once '../Interfaces/UsuarioDAO.php';
    require_once '../Models/usuario.php';
    class UsuarioDAOSImpl implements UsuarioDAO {

        private $conn;

        public function __construct() {
            $this->conn = new mysqli("localhost", "root", "", "GestionEmpleados");
        }
    
        public function getAllUsers() {
            $function_conn = $this->conn;
            $users = array();
            $stmt = $function_conn->query("Select * from Usuario");
            while ($row = $stmt->fetch_assoc()) {
                array_push($users, new Usuario(
                    $row["id_usuario"], $row["id_departamento"], 
                    $row["id_rol"], $row["nombre"], $row["apellido"], 
                    $row["fecha_nacimiento"], $row["fecha_ingreso"], 
                    $row["cargo"], $row["correo_electronico"], 
                    $row["username"], $row["password"], 
                    $row["numero_telefonico"], $row["direccion_imagen"], 
                    $row["sexo"], $row["estado_civil"], 
                    $row["fechacreacion"], $row["usuariocreacion"], 
                    $row["fechamodificacion"], $row["usuariomodificacion"]));
            }
            return $users;
        }

        public function AgregarUsuario($id_departamento, 
        $id_rol, $nombre,$apellido, $fecha_nacimiento, $fecha_ingreso, 
        $cargo, $correo_electronico, $username, $password, 
        $numero_telefonico, $direccion_imagen, $sexo, $estado_civil, 
        $fechacreacion, $usuariocreacion, 
        $fechamodificacion, $usuariomodificacion) {
            $function_conn = $this->conn; 
            $stmt = $function_conn->prepare("Insert into Usuario 
            (id_departamento, id_rol, nombre, apellido, 
              fecha_nacimiento, fecha_ingreso, cargo, correo_electronico, 
              username, password, numero_telefonico, direccion_imagen, sexo,
              estado_civil, fechacreacion, usuariocreacion, 
              fechamodificacion, usuariomodificacion) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?) ");
            $stmt->bind_param("iissssssssssssssss", $id_departamento, 
            $id_rol, $nombre,$apellido, $fecha_nacimiento, $fecha_ingreso, 
            $cargo, $correo_electronico, $username, $password, 
            $numero_telefonico, $direccion_imagen, $sexo, $estado_civil, 
            $fechacreacion, $usuariocreacion, 
            $fechamodificacion, $usuariomodificacion);
            $stmt->execute();
            echo "Nuevo usuario creado." . "<br>";
        }
    }

    $UsuarioDAO = new UsuarioDAOSImpl();

    // $UsuarioDAO->AgregarUsuario(1, 1, "Juan", "Perez", "1990-01-01", date("Y-m-d"), "Developer", "juan.perez@example.com", "juanperez", "password123", "1234567890", "path/to/image.jpg", "M", "Single", date("Y-m-d H:i:s"), "admin", date("Y-m-d H:i:s"), "admin");

    $users = $UsuarioDAO->getAllUsers();

    foreach ($users as $user) {
        echo $user->id_usuario . ": " . $user->id_departamento . " - " . $user->id_rol . " - " . $user->nombre . " - " . $user->apellido . " - " . $user->fecha_nacimiento . " - " . $user->fecha_ingreso . " - " . $user->cargo . " - " . $user->correo_electronico . " - " . $user->username . " - " . $user->password . " - " . $user->numero_telefonico . " - " . $user->direccion_imagen . " - " . $user->sexo . " - " . $user->estado_civil . " - " . $user->fechacreacion . " - " . $user->usuariocreacion . " - " . $user->fechamodificacion . " - " . $user->usuariomodificacion . "<br>";
    }

    
?>
