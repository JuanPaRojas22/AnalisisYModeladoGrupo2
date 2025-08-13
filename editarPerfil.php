<?php
ob_start();
session_start();
require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php';
include "template.php";

// Conexión a DB
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

$conn = mysqli_init();
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);

if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");

// Instancia DAO
$UsuarioDAO = new UsuarioDAOSImpl();

// Obtener ID del usuario a editar (POST primero, luego GET, si no sesión)
$user_id = $_POST['id_usuario'] ?? $_GET['id_usuario'] ?? $_SESSION['id_usuario'];

// Validar permisos
if ($_SESSION['id_rol'] != 2 && $user_id != $_SESSION['id_usuario']) {
    die("No tienes permisos para editar a este usuario.");
}

// Obtener datos del usuario
$user = $UsuarioDAO->getUserById($user_id);
if (!$user) {
    die("Usuario no encontrado.");
}

// Datos para selects
$ocupaciones = $conn->query("SELECT id_ocupacion, nombre_ocupacion FROM ocupaciones ORDER BY nombre_ocupacion");
$nacionalidades = $conn->query("SELECT id_nacionalidad, pais FROM nacionalidades ORDER BY pais");

// Historial vacaciones
$historial_vacaciones = $UsuarioDAO->getHistorialVacacionesByUserId($user_id);
$dias_vacaciones = $historial_vacaciones['DiasRestantes'] ?? 0;

// Procesar formulario
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
    $sexo = $_POST['sexo'] ?? '';
    $id_ocupacion = trim($_POST['id_ocupacion']);
    $id_nacionalidad = trim($_POST['id_nacionalidad']);
    $dias_vacaciones = (int)($_POST['dias_vacaciones'] ?? 0);
    $errores = [];

    if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Correo electrónico no válido.";
    }

    // Imagen
    $direccion_imagen = $user['direccion_imagen'];
    if (isset($_FILES['direccion_imagen']) && $_FILES['direccion_imagen']['error'] === UPLOAD_ERR_OK) {
        $direccion_imagen = file_get_contents($_FILES['direccion_imagen']['tmp_name']);
    }

    if (empty($errores)) {
        $resultado = $UsuarioDAO->updateUser(
            $nombre, $apellido, $fecha_nacimiento, $fecha_ingreso,
            $correo_electronico, $username, $numero_telefonico, $direccion_imagen,
            $sexo, $estado_civil, $direccion_domicilio, $id_ocupacion, $id_nacionalidad, $user_id
        );

        // Actualizar historial vacaciones
        $stmt = $conn->prepare("UPDATE historial_vacaciones SET DiasRestantes = ? WHERE id_usuario = ?");
        $stmt->bind_param("ii", $dias_vacaciones, $user_id);
        $stmt->execute();
        if ($stmt->affected_rows === 0) {
            $stmt->close();
            $stmt = $conn->prepare("INSERT INTO historial_vacaciones (id_usuario,DiasRestantes) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $dias_vacaciones);
            $stmt->execute();
        }
        $stmt->close();

        if ($resultado === true) {
            $_SESSION['mensaje_exito'] = "Usuario modificado con éxito✅.";
            // Recargar la página del mismo usuario
            header("Location: editarPerfil.php?id_usuario=" . $user_id);
            exit;
        } else {
            $errores[] = $resultado;
        }
    }

    if (!empty($errores)) {
        foreach ($errores as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }

    // Refrescar datos del usuario después de actualización
    $user = $UsuarioDAO->getUserById($user_id);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Editar Perfil</title>
<style>
/* Tus estilos aquí */
</style>
</head>
<body>
<div class="container">
    <h3>Editar Usuario</h3>
    <form action="editarPerfil.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($user['id_usuario']); ?>">

        <label>Nombre:</label>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>

        <label>Apellido:</label>
        <input type="text" name="apellido" value="<?php echo htmlspecialchars($user['apellido']); ?>" required>

        <label>Fecha de Nacimiento:</label>
        <input type="date" name="fecha_nacimiento" value="<?php echo htmlspecialchars($user['fecha_nacimiento']); ?>" required>

        <label>Fecha de Ingreso:</label>
        <input type="date" name="fecha_ingreso" value="<?php echo htmlspecialchars($user['fecha_ingreso']); ?>" required>

        <label>Correo Electrónico:</label>
        <input type="email" name="correo_electronico" value="<?php echo htmlspecialchars($user['correo_electronico']); ?>" required>

        <label>Foto de perfil:</label>
        <input type="file" name="direccion_imagen" accept="image/*">

        <label>Usuario:</label>
        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

        <label>Teléfono:</label>
        <input type="text" name="numero_telefonico" value="<?php echo htmlspecialchars($user['numero_telefonico']); ?>" required>

        <label>Dirección:</label>
        <input type="text" name="direccion_domicilio" value="<?php echo htmlspecialchars($user['direccion_domicilio']); ?>" required>

        <label>Estado Civil:</label>
        <select name="estado_civil" required>
            <option value="Soltero" <?php echo ($user['estado_civil']=='Soltero')?'selected':''; ?>>Soltero</option>
            <option value="Casado" <?php echo ($user['estado_civil']=='Casado')?'selected':''; ?>>Casado</option>
            <option value="Divorciado" <?php echo ($user['estado_civil']=='Divorciado')?'selected':''; ?>>Divorciado</option>
        </select>

        <label>Sexo:</label>
        <select name="sexo" required>
            <option value="M" <?php echo ($user['sexo']=='M')?'selected':''; ?>>M</option>
            <option value="F" <?php echo ($user['sexo']=='F')?'selected':''; ?>>F</option>
        </select>

        <label>Ocupación:</label>
        <select name="id_ocupacion">
        <?php while($row = $ocupaciones->fetch_assoc()) {
            $sel = ($row['id_ocupacion']==$user['id_ocupacion'])?'selected':'';
            echo "<option value='{$row['id_ocupacion']}' $sel>{$row['nombre_ocupacion']}</option>";
        } ?>
        </select>

        <label>Nacionalidad:</label>
        <select name="id_nacionalidad">
        <?php while($row = $nacionalidades->fetch_assoc()) {
            $sel = ($row['id_nacionalidad']==$user['id_nacionalidad'])?'selected':'';
            echo "<option value='{$row['id_nacionalidad']}' $sel>{$row['pais']}</option>";
        } ?>
        </select>

        <label>Días de Vacaciones:</label>
        <input type="number" name="dias_vacaciones" value="<?php echo htmlspecialchars($dias_vacaciones); ?>" min="0">

        <button type="submit">Guardar Cambios</button>
    </form>
</div>
</body>
</html>

<?php ob_end_flush(); ?>
