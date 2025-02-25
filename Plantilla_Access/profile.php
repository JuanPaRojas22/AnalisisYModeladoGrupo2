<?php
session_start();
require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php';
include "template.php";

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "GestionEmpleados");

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$UsuarioDAO = new UsuarioDAOSImpl();
$user_id = $_SESSION['id_usuario'];
$user = $UsuarioDAO->getUserById($user_id);
$ocupaciones = $conn->query("SELECT id_ocupacion, nombre_ocupacion FROM ocupaciones ORDER BY nombre_ocupacion");
$nacionalidades = $conn->query("SELECT id_nacionalidad, pais FROM nacionalidades ORDER BY pais");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $fecha_nacimiento = trim($_POST['fecha_nacimiento']);
    $fecha_ingreso = trim($_POST['fecha_ingreso']);
    $correo_electronico = trim($_POST['correo_electronico']);
    $username = trim($_POST['username']);
    $numero_telefonico = trim($_POST['numero_telefonico']);
    $direccion_domicilio = trim($_POST['direccion_domicilio']);
    $estado_civil = trim($_POST['estado_civil']);
    $sexo = trim($_POST['sexo']);
    $id_ocupacion = trim($_POST['id_ocupacion']);
    $id_nacionalidad = trim($_POST['id_nacionalidad']);
    $direccion_imagen = $_FILES['direccion_imagen'];
    $errores = [];

    if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Correo electrónico no válido.";
    }

    $direccion_imagen = $user['direccion_imagen'];
    // Manejo de la imagen
        if (isset($_FILES['direccion_imagen']) && $_FILES['direccion_imagen']['error'] === UPLOAD_ERR_OK) {
            // Leer el contenido del archivo y convertirlo en binario
            $direccion_imagen = file_get_contents($_FILES['direccion_imagen']['tmp_name']);
        } else {
            echo "<script>alert('No se subió ningún archivo o ocurrió un error.');</script>";
        }

    if (empty($errores)) {
        $UsuarioDAO->updateUser($nombre, $apellido, $fecha_nacimiento, $fecha_ingreso, $correo_electronico, $username, $numero_telefonico, $direccion_imagen, $sexo, $estado_civil, $direccion_domicilio, $id_ocupacion, $id_nacionalidad, $user_id);
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;
        $_SESSION['direccion_imagen'] = $direccion_imagen;
        echo "<p style='color: green;'>Actualización exitosa.</p>";
    } else {
        foreach ($errores as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="assets/css/bootstrap.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .profile-header {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .profile-header img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-right: 20px;
        }
        .profile-container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        #perfil-usuario {
            margin-top: 35px; 
            margin-left: 210px;
            width: 80%;
        }
        .header-section {
            background-color: #007bff;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            font-size: 20px;
            font-weight: bold;
        }
        .card.p-4.shadow-lg {
            padding: 40px;
            min-height: 500px;
        }
    </style>


</head>


<body>
    <div id="perfil-usuario" class="container mt-5">
        <div class="card p-4 shadow-lg">
            
            <div class="row">
                <!-- Imagen de perfil -->
                <div class="col-md-3 text-center">
                    <img src="<?php echo htmlspecialchars($user['direccion_imagen']); ?>" width="200" class="img-fluid rounded-circle">
                </div>
                
                <!-- Información del usuario -->
                <div class="col-md-9">
                    <h3 class="mb-4">Información del Usuario</h3>
                    <div class="row">
                        <!-- Botones -->
                        <br/>
                        <div class="mt-4">
                            <button class="btn btn-primary">Información del Usuario</button>
                        </div>
                        <br/>

                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Nombre y Apellido:</strong> <?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></p>
                                    <p><strong>Departamento:</strong> <?php echo htmlspecialchars($user['departamento_nombre']); ?></p>
                                    <p><strong>Fecha de nacimiento:</strong> <?php echo htmlspecialchars($user['fecha_nacimiento']); ?></p>
                                    <p><strong>Fecha de ingreso:</strong> <?php echo htmlspecialchars($user['fecha_ingreso']); ?></p>
                                    <p><strong>Correo:</strong> <?php echo htmlspecialchars($user['correo_electronico']); ?></p>
                                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Numero:</strong> <?php echo htmlspecialchars($user['numero_telefonico']); ?></p>
                                    <p><strong>Direccion:</strong> <?php echo htmlspecialchars($user['direccion_domicilio']); ?></p>
                                    <p><strong>Estado Civil:</strong> <?php echo htmlspecialchars($user['estado_civil']); ?></p>
                                    <p><strong>Sexo:</strong> <?php echo htmlspecialchars($user['sexo']); ?></p>
                                    <p><strong>Ocupación:</strong> <?php echo htmlspecialchars($user['Nombre_Ocupacion']); ?></p>
                                    <p><strong>Nacionalidad:</strong> <?php echo htmlspecialchars($user['Nombre_Pais']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a class="btn btn-secondary" href="editarPerfil.php?id=<?php echo $user['id_usuario']; ?>">Editar Información</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
