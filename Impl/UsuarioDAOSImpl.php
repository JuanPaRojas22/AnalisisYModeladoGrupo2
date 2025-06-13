<?php
require_once __DIR__ . '/../Interfaces/UsuarioDAO.php';
require_once __DIR__ . '/../Models/usuario.php';

class UsuarioDAOSImpl implements UsuarioDAO
{
    private $conexion;

    public function __construct()
     {
        $host = 'accespersoneldb.mysql.database.azure.com';
        $user = 'adminUser';
        $pass = 'admin123+';
        $db = 'gestionEmpleados';

        $ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

        if (!file_exists($ssl_ca)) {
            die("❌ Certificado SSL no encontrado en: $ssl_ca");
        }

        $this->conexion = mysqli_init();

        mysqli_ssl_set($this->conexion, NULL, NULL, $ssl_ca, NULL, NULL);
        mysqli_options($this->conexion, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);

        if (!$this->conexion->real_connect($host, $user, $pass, $db, 3306, NULL, MYSQLI_CLIENT_SSL)) {
            die("❌ Conexión SSL fallida: " . mysqli_connect_error());
        }

        echo "✅ Conexión SSL exitosa";

        // Comenta el cierre si querés seguir usando la conexión luego
        // $this->conexion->close();
    }

    public function getAllUsers()
    {
        $function_conn = $this->conn;
        $users = array();

        // Realiza la consulta SQL
        $stmt = $function_conn->query("SELECT u.*, d.Nombre AS departamento_nombre, r.nombre AS rol_nombre, 
                                        e.descripcion AS estado_descripcion, e.descripcion as estado, 
                                        nac.pais AS Nombre_Pais, ocup.nombre_ocupacion AS Nombre_Ocupacion
                                        FROM Usuario u
                                        JOIN departamento d ON u.id_departamento = d.id_departamento
                                        JOIN rol r ON u.id_rol = r.id_rol
                                        JOIN estado e ON u.id_estado = e.id_estado
                                        JOIN ocupaciones Ocup ON u.id_ocupacion = Ocup.id_ocupacion
                                        JOIN nacionalidades nac ON u.id_nacionalidad = nac.id_nacionalidad
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
        $sql = "SELECT u.*, d.nombre AS departamento_nombre, r.nombre AS rol_nombre , 
        e.descripcion AS estado, nac.pais AS Nombre_Pais, ocup.nombre_ocupacion AS Nombre_Ocupacion
        FROM usuario u
        JOIN departamento d ON u.id_departamento = d.id_departamento
        JOIN rol r ON u.id_rol = r.id_rol
        JOIN estado e ON u.id_estado = e.id_estado
        JOIN ocupaciones Ocup ON u.id_ocupacion = Ocup.id_ocupacion
        JOIN nacionalidades nac ON u.id_nacionalidad = nac.id_nacionalidad
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

    // Me obtendra el departamento actual del usuario actual para utilizarlo en la vista de solicitudes de vacaciones
    public function getUserDepartmentById($id_usuario){
        $sql = "SELECT id_departamento FROM usuario WHERE id_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        if(!$stmt){
            die("Error al preparar la consulta: " . $this->conn->error);
        }
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {    
            $user = $result->fetch_assoc();
            return $user; // Asegúrate de que $user['id_departamento'] existe
        } else {
            return null;
        }
    }
    

    public function getUserById($id_usuario)
    {
        $sql = "SELECT u.*, d.nombre AS departamento_nombre, r.nombre AS rol_nombre, e.descripcion AS estado, 
                            nac.pais AS Nombre_Pais, ocup.nombre_ocupacion AS Nombre_Ocupacion
                FROM usuario u
                JOIN departamento d ON u.id_departamento = d.id_departamento
                JOIN rol r ON u.id_rol = r.id_rol
                JOIN estado e ON u.id_estado = e.id_estado
                JOIN nacionalidades nac ON u.id_nacionalidad = nac.id_nacionalidad
                JOIN ocupaciones ocup ON u.id_ocupacion = ocup.id_ocupacion
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

    // Metodo para obtener en el details la vacacion del usuario seleccionado
    
    public function getVacacionesByUserId($id_usuario){
        $sql = "SELECT * FROM vacacion WHERE id_usuario = ? AND (id_estado_vacacion = 1 OR id_estado_vacacion = 4)";

        // Prepara la consulta
        $stmt = $this->conn->prepare($sql);

        // Enlaza el parámetro (i = entero)
        $stmt->bind_param("i", $id_usuario);

        // Ejecuta la consulta
        $stmt->execute();

        // Obtiene el resultado
        $result = $stmt->get_result();

        // Array para almacenar las vacaciones
        $vacaciones = [];

        // Recorre cada fila
        while ($row = $result->fetch_assoc()) {
            $vacaciones[] = $row;  // Agrega la fila asociativa al array
        }

        // Devuelve el array de vacaciones
        return $vacaciones;
    }
    
    // Método para obtener el historial de vacaciones de un usuario
    public function getHistorialVacacionesByUserId($id_usuario){
        $sql = "SELECT * FROM historial_vacaciones WHERE id_usuario = ?";

        // Prepara la consulta
        $stmt = $this->conn->prepare($sql);

        // Enlaza el parámetro (i = entero)
        $stmt->bind_param("i", $id_usuario);

        // Ejecuta la consulta
        $stmt->execute();

        // Obtiene el resultado
        $result = $stmt->get_result();

        // Array para almacenar el historial de vacaciones
        $historial_vacaciones = [];

        // Recorre cada fila
        while ($row = $result->fetch_assoc()) {
            $historial_vacaciones[] = $row;  // Agrega la fila asociativa al array
        }

        // Devuelve el array de historial de vacaciones
        return $historial_vacaciones;
    }

    public function getEstadoVacacionById($id_estado_vacacion){
        $sql = "SELECT * FROM estado_vacacion WHERE id_estado_vacacion = ?";

        // Prepara la consulta
        $stmt = $this->conn->prepare($sql);

        // Enlaza el parámetro (i = entero)
        $stmt->bind_param("i", $id_estado_vacacion);

        // Ejecuta la consulta
        $stmt->execute();

        // Obtiene el resultado
        $result = $stmt->get_result();

        // Verifica si se encontró el estado de vacación
        if ($result->num_rows > 0) {
            // Recupera los datos del estado de vacación
            $estado_vacacion = $result->fetch_assoc();

            // Devuelve el estado de vacación
            return $estado_vacacion;
        } else {
            // Si no se encuentra el estado de vacación
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

    // Funcion para poder actualizar un usuario
    public function updateUser($nombre, $apellido, $fecha_nacimiento, $fecha_ingreso, $correo_electronico, $username, $numero_telefonico, $direccion_imagen, $sexo, $estado_civil, $direccion_domicilio, $id_ocupacion, $id_nacionalidad, $id_usuario)
    {

        try {
            // Excepcion por si se intenta subir una imagen que no es de tipo jpg, png o gif
            if (isset($_FILES['direccion_imagen']) && $_FILES['direccion_imagen']['error'] === UPLOAD_ERR_OK) {
                $fileType = mime_content_type($_FILES['direccion_imagen']['tmp_name']);
                if (!in_array($fileType, ['image/jpeg', 'image/png', 'image/gif'])) {
                    throw new Exception('La imagen debe ser de tipo JPG, PNG o GIF.');
                }
            }

            // Excepcion por si se intenta subir una imagen mayor a 3MB
            if (isset($_FILES['direccion_imagen']) && $_FILES['direccion_imagen']['error'] === UPLOAD_ERR_OK) {
            $fileSize = $_FILES['direccion_imagen']['size'];
            if ($fileSize > 2 * 1024 * 1024) { // 3MB
                throw new Exception('La imagen no debe ser mayor a 3MB.');
            }
            }

            $conn = $this->conn;
            $query = "UPDATE Usuario SET nombre = ?, apellido = ?, fecha_nacimiento = ?, fecha_ingreso = ?,  correo_electronico = ?, username = ?, numero_telefonico = ?, direccion_imagen = ?, sexo = ?, estado_civil = ?, direccion_domicilio = ?, id_ocupacion = ?, id_nacionalidad = ? WHERE id_usuario = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssssssssiii", $nombre, $apellido, $fecha_nacimiento, $fecha_ingreso, $correo_electronico, $username, $numero_telefonico, $direccion_imagen, $sexo, $estado_civil, $direccion_domicilio, $id_ocupacion, $id_nacionalidad, $id_usuario);
            // En caso de algun error en la base de datos, se lanza una excepcion
            if (!$stmt->execute()) {
                throw new Exception("Error al actualizar el usuario: " . $stmt->error);
            }
            return true;

        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
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
?>
