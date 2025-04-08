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
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;  /* Blanco cremoso */
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            background-color: #f7f7f7;  /* Blanco cremoso */
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.8);
        }

        .card {
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.8);
        }

        .img-fluid {
            border-radius: 50%;
            width: 150px;
            height: 150px;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px;
            border-radius: 5px;
        }

        .btn {
            display: inline-block;
            background-color: #c9aa5f;
            color: white;
            padding: 12px 20px;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            margin-left: 50%;
            transition: background-color 0.3s;
            cursor: pointer;
            border: none;
        }
    </style>
</head>

<body>
    <div class="container">

        <div class="row">
            <div class="col-md-3 text-center">
                <img src="<?php echo htmlspecialchars($user['direccion_imagen']); ?>" class="img-fluid">
            </div>
            <div class="col-md-9">
                <h3>Información del Usuario</h3>
                <form action="profile.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($user['id_usuario']); ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <label>Nombre:</label>
                            <input type="text" class="form-control" name="nombre"
                                value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                            <label>Apellido:</label>
                            <input type="text" class="form-control" name="apellido"
                                value="<?php echo htmlspecialchars($user['apellido']); ?>" required>
                            <label>Fecha de Nacimiento:</label>
                            <input type="date" class="form-control" name="fecha_nacimiento"
                                value="<?php echo htmlspecialchars($user['fecha_nacimiento']); ?>" required>
                            <label>Fecha de Ingreso:</label>
                            <input type="date" class="form-control" name="fecha_ingreso"
                                value="<?php echo htmlspecialchars($user['fecha_ingreso']); ?>" required>
                            <label>Correo Electrónico:</label>
                            <input type="email" class="form-control" name="correo_electronico"
                                value="<?php echo htmlspecialchars($user['correo_electronico']); ?>" required>
                            <div class="form-group">
                                <label for="direccion_imagen">Foto de perfil:</label>
                                <input type="file" class="form-control" id="direccion_imagen" name="direccion_imagen"
                                    accept="image/*">
                            </div>

                        </div>
                        <div class="col-md-6">
                            <label>Usuario:</label>
                            <input type="text" class="form-control" name="username"
                                value="<?php echo htmlspecialchars($user['username']); ?>" required>
                            <label>Teléfono:</label>
                            <input type="text" class="form-control" name="numero_telefonico"
                                value="<?php echo htmlspecialchars($user['numero_telefonico']); ?>" required>
                            <label>Dirección:</label>
                            <input type="text" class="form-control" name="direccion_domicilio"
                                value="<?php echo htmlspecialchars($user['direccion_domicilio']); ?>" required>
                            <label>Estado Civil:</label>
                            <select name="estado_civil" class="form-control" required>
                                <option value="">Seleccione estado civil</option>
                                <option value="Soltero" <?php echo ($user['estado_civil'] == 'Soltero') ? 'selected' : ''; ?>>Soltero</option>
                                <option value="Casado" <?php echo ($user['estado_civil'] == 'Casado') ? 'selected' : ''; ?>>Casado</option>
                                <option value="Divorciado" <?php echo ($user['estado_civil'] == 'Divorciado') ? 'selected' : ''; ?>>Divorciado</option>
                            </select>
                            <label>Sexo:</label>
                            <select name="sexo" class="form-control" required>
                                <option value="M" <?php echo ($user['sexo'] == 'M') ? 'selected' : ''; ?>>M</option>
                                <option value="F" <?php echo ($user['sexo'] == 'F') ? 'selected' : ''; ?>>F</option>
                            </select>
                            <label>Ocupación:</label>
                            <select name="id_ocupacion" class="form-control">
                                <?php while ($row = $ocupaciones->fetch_assoc()) {
                                    $selected = ($row['id_ocupacion'] == $user['id_ocupacion']) ? 'selected' : '';
                                    echo '<option value="' . $row['id_ocupacion'] . '" ' . $selected . '>' . $row['nombre_ocupacion'] . '</option>';
                                } ?>
                            </select>
                            <label>Nacionalidad:</label>
                            <select name="id_nacionalidad" class="form-control">
                                <?php while ($row = $nacionalidades->fetch_assoc()) {
                                    $selected = ($row['id_nacionalidad'] == $user['id_nacionalidad']) ? 'selected' : '';
                                    echo '<option value="' . $row['id_nacionalidad'] . '" ' . $selected . '>' . $row['pais'] . '</option>';
                                } ?>
                            </select>
                        </div>

                    </div>
            </div>
            <button type="submit" class="btn"><i class="bi bi-arrow-clockwise"></i></button>
            </form>
        </div>
    </div>
    </div>
    </div>
</body>

</html>