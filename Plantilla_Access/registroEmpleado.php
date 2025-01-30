<?php
// Conexión a la base de datos
require 'conexion.php';
session_start();


// Procesar el formulario cuando se envíe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos del formulario
    $nombre = $_POST['nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $cargo = $_POST['cargo'] ?? '';
    $correo_electronico = $_POST['correo_electronico'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? '';
    $departamento = $_POST['departamento'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $fecha_ingreso = $_POST['fecha_ingreso'] ?? '';
    $numero_telefonico = $_POST['numero_telefonico'] ?? '';
    $direccion_imagen = $_POST['direccion_imagen'] ?? '';
    $sexo = $_POST['sexo'] ?? '';
    $estado_civil = $_POST['estado_civil'] ?? '';

    // Validar campos obligatorios
    $errores = [];
    if (empty($nombre)) $errores[] = "El nombre es obligatorio.";
    if (empty($apellido)) $errores[] = "El apellido es obligatorio.";
    if (empty($cargo)) $errores[] = "El cargo es obligatorio.";
    if (empty($correo_electronico)) $errores[] = "El correo electrónico es obligatorio.";
    if (empty($username)) $errores[] = "El nombre de usuario es obligatorio.";
    if (empty($password)) $errores[] = "La contraseña es obligatoria.";
    if (empty($rol)) $errores[] = "Debe seleccionar un rol.";
    if (empty($departamento)) $errores[] = "Debe seleccionar un departamento.";
    if (empty($fecha_nacimiento)) $errores[] = "La fecha de nacimiento es obligatoria.";
    if (empty($fecha_ingreso)) $errores[] = "La fecha de ingreso es obligatoria.";

    // Si hay errores, mostrarlos
    if (!empty($errores)) {
        foreach ($errores as $error) {
            echo "<script>alert('$error');</script>";
        }
    } else {
        // Asignar el id del rol según la selección
        switch ($rol) {
            case 'usuario':
                $rol = 1;
                break;
            case 'admin_regular':
                $rol = 2;
                break;
            default:
                $rol = 1; // Valor predeterminado
        }

        // Preparar la consulta SQL
        $stmt = $conn->prepare("INSERT INTO Usuario (nombre, apellido, cargo, correo_electronico, username, password, id_rol, id_departamento, fecha_nacimiento, fecha_ingreso, numero_telefonico, direccion_imagen, sexo, estado_civil, fechacreacion, usuariocreacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), ?)");

        if ($stmt) {
            // Encriptar la contraseña antes de guardarla
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Asignar valores a los parámetros
            $usuario_creacion = 'Admin'; // Valor predeterminado para el usuario que crea el registro
            $stmt->bind_param("ssssssiiissssss", $nombre, $apellido, $cargo, $correo_electronico, $username, $password_hash, $rol, $departamento, $fecha_nacimiento, $fecha_ingreso, $numero_telefonico, $direccion_imagen, $sexo, $estado_civil, $usuario_creacion);

            // Ejecutar la consulta
            if ($stmt->execute()) {
                echo "<script>alert('Empleado registrado exitosamente.');</script>";
                header("Location: index.php");
                exit;
            } else {
                echo "<script>alert('Error al registrar el empleado: " . $stmt->error . "');</script>";
            }

            $stmt->close();
        } else {
            echo "<script>alert('Error en la preparación de la consulta: " . $conn->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Empleados</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2 class="text-center">Registrar Empleados</h2>
    <form action="" method="POST" class="form-horizontal">
        <div class="form-group">
            <label for="nombre" class="control-label">Nombre:</label>
            <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Ingrese el nombre">
        </div>

        <div class="form-group">
            <label for="apellido" class="control-label">Apellido:</label>
            <input type="text" id="apellido" name="apellido" class="form-control" placeholder="Ingrese el apellido">
        </div>

        <div class="form-group">
            <label for="cargo" class="control-label">Cargo:</label>
            <input type="text" id="cargo" name="cargo" class="form-control" placeholder="Ingrese el cargo">
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

        <div class="form-group">
            <label for="rol" class="control-label">Rol:</label>
            <select id="rol" name="rol" class="form-control">
                <option value="">Seleccione un rol</option>
                <option value="usuario">Usuario</option>
                <option value="admin_regular">Administrador Regular</option>
            </select>
        </div>

        <div class="form-group">
            <label for="departamento" class="control-label">Departamento:</label>
            <select id="departamento" name="departamento" class="form-control">
                <option value="">Seleccione un departamento</option>
                <option value="1">Recursos Humanos</option>
                
            </select>
        </div>

        <div class="form-group">
            <label for="fecha_nacimiento" class="control-label">Fecha de Nacimiento:</label>
            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control">
        </div>

        <div class="form-group">
            <label for="fecha_ingreso" class="control-label">Fecha de Ingreso:</label>
            <input type="date" id="fecha_ingreso" name="fecha_ingreso" class="form-control">
        </div>

        <div class="form-group">
            <label for="numero_telefonico" class="control-label">Número Telefónico:</label>
            <input type="text" id="numero_telefonico" name="numero_telefonico" class="form-control" placeholder="Ingrese el número telefónico">
        </div>

        <div class="form-group">
            <label for="direccion_imagen" class="control-label">Dirección de Imagen:</label>
            <input type="text" id="direccion_imagen" name="direccion_imagen" class="form-control" placeholder="Ingrese la dirección de la imagen">
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