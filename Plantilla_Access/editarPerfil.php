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

// Instancia el DAO
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
    $sexo = isset($_POST['sexo']) ? trim($_POST['sexo']) : '';
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
// Verifica si el parámetro 'id' está presente en la URL
if (isset($_GET['id'])) {
    $id_usuario = $_GET['id'];

    // Obtiene los detalles del usuario por id
    $user = $UsuarioDAO->getUserById($id_usuario);

    // Obtiene las vacaciones del usuario actual
    $vacaciones = $UsuarioDAO->getVacacionesByUserId($id_usuario);

    // Obtiene los historiales de vacaciones del usuario actual
    $historial_vacaciones = $UsuarioDAO->getHistorialVacacionesByUserId($id_usuario);

    

    // Si el usuario no existe
    if (!$user) {
        echo "Usuario no encontrado.";
        exit;
    }
    } else {
        echo "ID de usuario no proporcionado.";
        exit;
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
            min-height: 600px;
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

                        <form action="profile.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($user['id_usuario']); ?>">
                            <div class="col-md-9">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nombre">Nombre:</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="apellido">Apellido:</label>
                                            <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($user['apellido']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo htmlspecialchars($user['fecha_nacimiento']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="fecha_ingreso">Fecha de Ingreso:</label>
                                            <input type="date" class="form-control" id="fecha_ingreso" name="fecha_ingreso" value="<?php echo htmlspecialchars($user['fecha_ingreso']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="correo_electronico">Correo Electrónico:</label>
                                            <input type="email" class="form-control" id="correo_electronico" name="correo_electronico" value="<?php echo htmlspecialchars($user['correo_electronico']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="username">Nombre de Usuario:</label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="direccion_imagen">Foto de perfil:</label>
                                            <input type="file" class="form-control" id="direccion_imagen" name="direccion_imagen" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="numero_telefonico">Número Telefónico:</label>
                                            <input type="text" class="form-control" id="numero_telefonico" name="numero_telefonico" value="<?php echo htmlspecialchars($user['numero_telefonico']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="direccion_domicilio">Dirección de Domicilio:</label>
                                            <input type="text" class="form-control" id="direccion_domicilio" name="direccion_domicilio" value="<?php echo htmlspecialchars($user['direccion_domicilio']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="estado_civil">Estado Civil:</label>
                                            <select id="estado_civil" name="estado_civil" class="form-control" required>
                                                <option value="">Seleccione estado civil</option>
                                                <option value="Soltero" <?php echo ($user['estado_civil'] == 'Soltero') ? 'selected' : ''; ?>>Soltero</option>
                                                <option value="Casado" <?php echo ($user['estado_civil'] == 'Casado') ? 'selected' : ''; ?>>Casado</option>
                                                <option value="Divorciado" <?php echo ($user['estado_civil'] == 'Divorciado') ? 'selected' : ''; ?>>Divorciado</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="sexo">Sexo:</label>
                                            <select id="sexo" name="sexo" class="form-control" required>
                                                <option value="M" <?php echo ($user['sexo'] == 'M') ? 'selected' : ''; ?>>M</option>
                                                <option value="F" <?php echo ($user['sexo'] == 'F') ? 'selected' : ''; ?>>F</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="id_ocupacion">Ocupación:</label>
                                            <select id="id_ocupacion" name="id_ocupacion" class="form-control">
                                                <?php while ($row = $ocupaciones->fetch_assoc()) {
                                                    $selected = ($row['id_ocupacion'] == $user['id_ocupacion']) ? 'selected' : '';
                                                    echo '<option value="' . $row['id_ocupacion'] . '" ' . $selected . '>' . $row['nombre_ocupacion'] . '</option>';
                                                } ?>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="id_nacionalidad">Nacionalidad:</label>
                                            <select id="id_nacionalidad" name="id_nacionalidad" class="form-control">
                                                <?php while ($row = $nacionalidades->fetch_assoc()) {
                                                    $selected = ($row['id_nacionalidad'] == $user['id_nacionalidad']) ? 'selected' : '';
                                                    echo '<option value="' . $row['id_nacionalidad'] . '" ' . $selected . '>' . $row['pais'] . '</option>';
                                                } ?>
                                            </select>
                                        </div>
                                        <br/>
                                        <button type="submit" class="btn btn-primary mt-3">Actualizar</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
