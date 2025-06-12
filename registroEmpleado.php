<?php
// Conexión a la base de datos
//require 'conexion.php';
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['id_rol'] == 3 OR $_SESSION['id_rol'] == 1) { // Verificar si el usuario es un empleado
    header("Location: index.php"); // Redirigir a la página de inicio si no es administrador
    exit;
}
                                       
require 'template.php';
// Conexión a la base de datos
// Parámetros de conexión
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

// Ruta al certificado CA para validar SSL
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

// Inicializamos mysqli
$conn = mysqli_init();

// Configuramos SSL
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);


// Intentamos conectar usando SSL (con la bandera MYSQLI_CLIENT_SSL)
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecemos el charset
mysqli_set_charset($conn, "utf8mb4");
// Procesar el formulario cuando se envíe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos del formulario
    $id_departamento = $_POST['id_departamento'] ?? '';
    $id_rol = $_POST['id_rol'] ?? ''; // Rol predeterminado de usuario
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $fecha_ingreso = date("Y-m-d");
    $correo_electronico = trim($_POST['correo_electronico'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $numero_telefonico = trim($_POST['numero_telefonico'] ?? '');
    $sexo = $_POST['sexo'] ?? '';
    $estado_civil = $_POST['estado_civil'] ?? '';
    $id_ocupacion = $_POST['id_ocupacion'] ?? '';
    $id_nacionalidad = $_POST['id_nacionalidad'] ?? '';
    $id_estado = $_POST['id_estado'] ?? 1; // Estado activo por defecto
    $direccion_domicilio = trim($_POST['direccion_domicilio'] ?? '');
    $fechacreacion = date("Y-m-d H:i:s");
    $usuariocreacion = "admin"; 
    $fechamodificacion = date("Y-m-d H:i:s");
    $usuariomodificacion = "admin";

    // Validar campos obligatorios
    if (
        empty($id_departamento) || empty($nombre) || empty($apellido) || 
        empty($correo_electronico) || empty($username) || empty($password)  
        
    ) {
        echo "<script>alert('Por favor, complete todos los campos obligatorios.');</script>";
    } else {
        // Excepcion para aceptar solo cierto tipo de archivos de imagen
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['direccion_imagen']['tmp_name']);
        $fileName = $_FILES['direccion_imagen']['name'];
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        // Validar el tipo MIME y la extensión
        if (!in_array($fileType, $allowedTypes) || !in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif'])) {
            echo "<script>
                        alert('Solo se permiten archivos de imagen JPG, PNG o GIF.');
                        window.location.href = 'createUser.php';
                  </script>";
            exit;
        }
        

        // Manejo de la imagen (por defecto NULL)
        $direccion_imagen = null;
        if (isset($_FILES['direccion_imagen']) && $_FILES['direccion_imagen']['error'] === UPLOAD_ERR_OK) {
            $direccion_imagen = file_get_contents($_FILES['direccion_imagen']['tmp_name']);
        }

        // Preparar la consulta SQL
        $stmt = $conn->prepare("INSERT INTO Usuario 
            (id_departamento, id_rol, nombre, apellido, 
            fecha_nacimiento, fecha_ingreso, correo_electronico, 
            username, password, numero_telefonico, direccion_imagen, 
            sexo, estado_civil, fechacreacion, usuariocreacion, 
            fechamodificacion, usuariomodificacion, id_estado, direccion_domicilio, 
            id_ocupacion, id_nacionalidad) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Encriptar la contraseña antes de guardarla
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // EXCEPCIONES 
        // 1. Verificar si el correo electronico o username ya existen en la base de datos
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM Usuario WHERE correo_electronico = ? OR username = ?");
        $checkStmt->bind_param("ss", $correo_electronico, $username);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();
        // Se verifica si la cantidad de registros es mayor a 0, lo que indica que ya existe un usuario con ese correo o username.
        if ($count > 0) {
            echo "<script>
                    alert('El correo electrónico o el nombre de usuario ya están en uso. Por favor, elija otro.');
                    window.location.href = 'registroEmpleado.php';
                  </script>";
            exit; // Terminar la ejecución del script si ya existe el usuario

        }

        // 2. Verificar que solo se coloquen letras, numeros y _ (es decir nada de caracteres especiales) en el campo de username
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            echo "<script>
                    alert('El nombre de usuario solo puede contener letras, números y guiones bajos.');
                    window.location.href = 'registroEmpleado.php';
                  </script>";
            exit; // Terminar la ejecución del script si ya existe el usuario
        }

        // 3. Verificar que solo se coloquen letras (es decir nada de caracteres especiales) en el campo de nombre y apellido
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ ]+$/', $nombre) || !preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ ]+$/', $apellido)) {
            echo "<script>
                        alert('El nombre y el apellido solo pueden contener letras y espacios.');
                        window.location.href = 'registroEmpleado.php';
                  </script>";
            exit; // Terminar la ejecución del script si ya existe el usuario
        }


        if ($stmt) {
            // Asignar los parámetros recibidos por el formulario
            $stmt->bind_param("iissssssssbssssssissi",
                $id_departamento,
                $id_rol,
                $nombre,
                $apellido,
                $fecha_nacimiento,
                $fecha_ingreso,
                $correo_electronico,
                $username,
                $password_hash,
                $numero_telefonico,
                $direccion_imagen,
                $sexo,
                $estado_civil,
                $fechacreacion,
                $usuariocreacion,
                $fechamodificacion,
                $usuariomodificacion,
                $id_estado,
                $direccion_domicilio,
                $id_ocupacion,
                $id_nacionalidad
            );

            // Enviar la imagen como datos binarios si existe
            if ($direccion_imagen !== null) {
                $stmt->send_long_data(10, $direccion_imagen);
            }

            // Ejecutar la consulta
            if ($stmt->execute()) {
                echo "<script>alert('Usuario registrado exitosamente.');</script>";
                echo "<script>window.location.href = 'MostrarUsuarios.php';</script>";
            } else {
                echo "<script>alert('Error al registrar usuario: " . $stmt->error . "');</script>";
            }

            // Cerrar la declaración
            $stmt->close();
        } else {
            echo "<script>alert('Error en la preparación de la consulta: " . $conn->error . "');</script>";
        }
    }
}

// Obtener departamentos, ocupaciones y nacionalidades
$departamentos = $conn->query("SELECT id_departamento, Nombre FROM Departamento");
$ocupaciones = $conn->query("SELECT id_ocupacion, nombre_ocupacion FROM ocupaciones ORDER BY nombre_ocupacion");
$nacionalidades = $conn->query("SELECT id_nacionalidad, pais FROM nacionalidades ORDER BY pais");
$roles = $conn->query("SELECT id_rol, nombre FROM rol ORDER BY nombre");
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Empleados</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        td, div {
            color: black !important;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="text-center">Registrar Empleados</h2>
    <form action="" method="POST" class="form-horizontal" enctype="multipart/form-data">
        <div class="form-group">
            <label for="rol" class="control-label">Departamento:</label>
            <select id="id_departamento" name="id_departamento" class="form-control">
                    <?php while ($row = $departamentos->fetch_assoc()) {
                        echo '<option value="' . $row['id_departamento'] . '">' . $row['Nombre'] . '</option>';
                    } ?>
                </select>
        </div>

        <div class="form-group">
            <label for="rol" class="control-label">Ocupacion:</label>
            <select id="id_nacionalidad" name="id_nacionalidad" class="form-control">
                    <?php while ($row = $nacionalidades->fetch_assoc()) {
                        echo '<option value="' . $row['id_nacionalidad'] . '">' . $row['pais'] . '</option>';
                    } ?>
                </select>
        </div>

        <div class="form-group">
            <label for="rol" class="control-label">Nacionalidad:</label>
            <select id="id_ocupacion" name="id_ocupacion" class="form-control">
                    <?php while ($row = $ocupaciones->fetch_assoc()) {
                        echo '<option value="' . $row['id_ocupacion'] . '">' . $row['nombre_ocupacion'] . '</option>';
                    } ?>
                </select>
        </div>           

        <div class="form-group">
            <label for="rol" class="control-label">Rol:</label>
            <select id="id_rol" name="id_rol" class="form-control">
                    <?php while ($row = $roles->fetch_assoc()) {
                        echo '<option value="' . $row['id_rol'] . '">' . $row['nombre'] . '</option>';
                    } ?>
                </select>
        </div>

        

        <div class="form-group">
            <label for="nombre" class="control-label">Nombre:</label>
            <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ingrese el nombre">
        </div>

        <div class="form-group">
            <label for="apellido" class="control-label">Apellido:</label>
            <input type="text" id="apellido" name="apellido" class="form-control" placeholder="Ingrese el apellido">
        </div>

        <div class="form-group">
            <label for="fecha_nacimiento" class="control-label">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control">
        </div>
        
        <div class="form-group">
            <label for="correo_electronico" class="control-label">Correo Electrónico:</label>
            <input type="email" id="correo_electronico" name="correo_electronico" class="form-control" placeholder="Ingrese el correo electrónico">
        </div>

        <div class="form-group">
            <label for="username" class="control-label">Usuario:</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="Ingrese el nombre de usuario">
        </div>

        <div class="form-group">
            <label for="password" class="control-label">Contraseña:</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="Ingrese la contraseña">
        </div>
        
        <!--
        <div class="form-group">
            <label for="fecha_ingreso" class="control-label">Fecha de Ingreso:</label>
            <input type="date" id="fecha_ingreso" name="fecha_ingreso" class="form-control">
        </div>
        -->

        <div class="form-group">
            <label for="numero_telefonico" class="control-label">Número Telefónico:</label>
            <input type="text" id="numero_telefonico" name="numero_telefonico" class="form-control" placeholder="Ingrese el número telefónico">
        </div>

        <div class="form-group">
             <label for="direccion_imagen">Foto Perfil:</label>
		     <input type="file" id="direccion_imagen" name="direccion_imagen" class="form-control" placeholder="Ingrese su foto de perfil" autofocus>
                    
        </div>

        <div class="form-group">
            <label for="sexo" class="control-label">Sexo:</label>
            <select id="sexo" name="sexo" class="form-control">
                <option value="">Seleccione sexo</option>
                <option value="Masculino">Masculino</option>
                <option value="Femenino">Femenino</option>
            </select>
        </div>

        <div class="form-group">
            <label for="estado_civil" class="control-label">Estado Civil:</label>
            <select id="estado_civil" name="estado_civil" class="form-control">
                <option value="">Seleccione estado civil</option>
                <option value="Soltero">Soltero</option>
                <option value="Casado">Casado</option>
                <option value="Divorciado">Divorciado</option>
            </select>
        </div>

        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">Registrar</button>
        </div>
    </form>
</div>

<script src="assets/js/jquery.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
</body>
</html>
