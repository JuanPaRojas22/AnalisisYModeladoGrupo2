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
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>Gestión de Usuarios</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />

    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        .profile-container {
            margin-left: 250px;
            padding: 60px;
        }
    </style>
</head>

<body>

    <section id="container">
        <!-- **********************************************************************************************************************************************************
      TOP BAR CONTENT & NOTIFICATIONS
      *********************************************************************************************************************************************************** -->
        <!--header start-->
        
        <div class="profile-container">
        <h2>Actualizar Perfil</h2>
        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
            <br>
            <label>Apellido:</label>
            <input type="text" name="apellido" value="<?php echo htmlspecialchars($user['apellido']); ?>" required>
            <br>
            <label>Fecha de Nacimiento:</label>
            <input type="date" name="fecha_nacimiento" value="<?php echo htmlspecialchars($user['fecha_nacimiento']); ?>" required>
            <br>
            <label>Fecha de Ingreso:</label>
            <input type="date" name="fecha_ingreso" value="<?php echo htmlspecialchars($user['fecha_ingreso']); ?>" required>
            <br>
            <label>Correo Electrónico:</label>
            <input type="email" name="correo_electronico" value="<?php echo htmlspecialchars($user['correo_electronico']); ?>" required>
            <br>
            <label>Username:</label>
            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            <br>
            <label>Teléfono:</label>
            <input type="text" name="numero_telefonico" value="<?php echo htmlspecialchars($user['numero_telefonico']); ?>" required>
            <br>
            <label>Dirección:</label>
            <input type="text" name="direccion_domicilio" value="<?php echo htmlspecialchars($user['direccion_domicilio']); ?>" required>
            <br>
            <label>Estado Civil:</label>
            <input type="text" name="estado_civil" value="<?php echo htmlspecialchars($user['estado_civil']); ?>" required>
            <br>
            <label>Sexo:</label>
            <input type="text" name="sexo" value="<?php echo htmlspecialchars($user['sexo']); ?>" required>
            <br>
            <label for="id_ocupacion">Ocupación:</label>
                <select id="id_ocupacion" name="id_ocupacion" class="form-control">
                    <?php while ($row = $ocupaciones->fetch_assoc()) {
                        $selected = ($row['id_ocupacion'] == $user['id_ocupacion']) ? 'selected' : '';
                        echo '<option value="' . $row['id_ocupacion'] . '" ' . $selected . '>' . $row['nombre_ocupacion'] . '</option>';
                    } ?>
                </select>

            <label for="id_nacionalidad">Nacionalidad:</label>
                <select id="id_nacionalidad" name="id_nacionalidad" class="form-control">
                    <?php while ($row = $nacionalidades->fetch_assoc()) {
                        $selected = ($row['id_nacionalidad'] == $user['id_nacionalidad']) ? 'selected' : '';
                        echo '<option value="' . $row['id_nacionalidad'] . '" ' . $selected . '>' . $row['pais'] . '</option>';
                    } ?>
                </select>
            <label>Foto de perfil:</label>
            <input type="file" name="direccion_imagen" accept="image/*">
            <br>
            <button type="submit">Actualizar</button>
        </form>
            <img src="<?php echo htmlspecialchars($user['direccion_imagen']); ?>" width="100">
        </div>

</body>

</html>