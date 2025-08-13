<?php
session_start();
require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php';
include "template.php";
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Obtener el ID del usuario a editar del parámetro GET
$user_id = $_POST['id'] ?? $_SESSION['id_usuario'];

// Si no hay ID en GET y el usuario no es administrador master, redirigir
if (!$user_id && $_SESSION['id_rol'] != 2) {
    header("Location: index.php");
    exit;
}
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

$UsuarioDAO = new UsuarioDAOSImpl();

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
        body {
            font-family: 'Ruda', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        .profile-container {
            width: 40%;
            max-width: 2000px;
            margin: 50px auto 00px 250px;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
            margin-left: 35%;
        }
        .header-section {
            background-color: #106469;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            font-size: 22px;
            font-weight: bold;
        }
        .user-img {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }
        .user-img img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #116B67;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            padding: 20px;
        }
        .info-column {
            width: 48%;
        }
        .info-column p {
            font-size: 18px;
            color: #555;
            margin: 5px 0;
        }
        .btn-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .btn {
            background-color: #0B4F6C;
            color: white;
            padding: 12px 20px;
            font-size: 18px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
            text-decoration: none;
        }
        .btn:hover {
            background-color: darkgray;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="header-section">Perfil de Usuario</div>
        <div class="user-img">
            <img src="<?php echo htmlspecialchars($user['direccion_imagen']); ?>" alt="Foto de perfil">
        </div>
        <div class="info-section">
            <div class="info-column">
                <p><strong>Nombre y Apellido:</strong> <?php echo htmlspecialchars($user['nombre'] . ' ' . $user['apellido']); ?></p>
                <p><strong>Departamento:</strong> <?php echo htmlspecialchars($user['departamento_nombre']); ?></p>
                <p><strong>Fecha de nacimiento:</strong> <?php echo htmlspecialchars($user['fecha_nacimiento']); ?></p>
                <p><strong>Fecha de ingreso:</strong> <?php echo htmlspecialchars($user['fecha_ingreso']); ?></p>
                <p><strong>Correo:</strong> <?php echo htmlspecialchars($user['correo_electronico']); ?></p>
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            </div>
            <div class="info-column">
                <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($user['numero_telefonico']); ?></p>
                <p><strong>Dirección:</strong> <?php echo htmlspecialchars($user['direccion_domicilio']); ?></p>
                <p><strong>Estado Civil:</strong> <?php echo htmlspecialchars($user['estado_civil']); ?></p>
                <p><strong>Sexo:</strong> <?php echo htmlspecialchars($user['sexo']); ?></p>
                <p><strong>Ocupación:</strong> <?php echo htmlspecialchars($user['Nombre_Ocupacion']); ?></p>
                <p><strong>Nacionalidad:</strong> <?php echo htmlspecialchars($user['Nombre_Pais']); ?></p>
            </div>
        </div>
        <div class="btn-container">
            <a class="btn" href="editarPerfil.php?id=<?php echo $user['id_usuario']; ?>">Editar Información</a>
        </div>
    </div>
</body>
</html>
