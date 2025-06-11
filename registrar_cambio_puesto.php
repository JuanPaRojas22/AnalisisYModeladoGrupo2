<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "gestionempleados");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar autenticación del usuario
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['id_usuario'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Invitado';

// Incluir la plantilla
include 'template.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Registrar Cambio de Puesto</title>
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 50px auto; /* centrado */
            padding: 25px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            color: black;
        }

        h1 {
            text-align: center;
            color: black;
            margin-bottom: 30px;
        }

        .button {
            display: inline-block;
            background-color: #147964;
            color: black;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }

        form {
            width: 100%;
        }

        label {
            font-size: 16px;
            color: black;
            margin-bottom: 8px;
            display: block;
        }

        input, textarea, button, select {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            color: black; /* for input text */
            background-color: white;
        }

        textarea {
            resize: vertical;
        }

        button {
            background-color: #147964;
            color: black;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }

        p {
            font-weight: bold;
            color: #116B67;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="ver_historial_cambios.php" class="button">Historial de Cambios</a>

        <h1>Registrar Cambio de Puesto</h1>

        <?php
        $conn = new mysqli("localhost", "root", "", "gestionempleados");
        if ($conn->connect_error) {
            die("Error de conexión: " . $conn->connect_error);
        }

        $mensaje = "";

        $query = "SELECT id_usuario, nombre, apellido FROM Usuario";
        $result = $conn->query($query);
        $usuarios = ($result->num_rows > 0) ? $result->fetch_all(MYSQLI_ASSOC) : [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_usuario     = $_POST['id_usuario'];
            $nuevo_puesto   = $_POST['nuevo_puesto'];
            $sueldo_nuevo   = $_POST['sueldo_nuevo'];
            $motivo         = $_POST['motivo'];
            $fecha_cambio   = $_POST['fecha_cambio'];

            if (!is_numeric($sueldo_nuevo) || $sueldo_nuevo <= 0) {
                $mensaje = "El sueldo nuevo debe ser un valor numérico mayor que 0.";
            } else {
                $query = "INSERT INTO historial_cargos (
                            id_usuario, 
                            nuevo_puesto, 
                            sueldo_nuevo, 
                            motivo, 
                            fecha_cambio, 
                            fechacreacion, 
                            usuariocreacion
                        ) VALUES (
                            '$id_usuario', 
                            '$nuevo_puesto', 
                            '$sueldo_nuevo', 
                            '$motivo', 
                            '$fecha_cambio', 
                            CURDATE(), 
                            'usuario_logueado'
                        )";

                if ($conn->query($query) === TRUE) {
                    $mensaje = "Cambio de puesto registrado con éxito.";
                } else {
                    $mensaje = "Error al registrar el cambio de puesto: " . $conn->error;
                }
            }
        }
        ?>

        <?php if ($mensaje): ?>
            <p><?php echo $mensaje; ?></p>
        <?php endif; ?>

        <form action="registrar_cambio_puesto.php" method="POST">
            <label for="id_usuario">Seleccione el Usuario:</label>
            <select name="id_usuario" required>
                <option value="">Seleccione un usuario</option>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo $usuario['id_usuario']; ?>">
                        <?php echo $usuario['nombre'] . ' ' . $usuario['apellido']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="puesto_anterior">Puesto Anterior (ID de ocupación):</label>
            <input type="text" name="puesto_anterior" id="puesto_anterior" readonly>

            <label for="sueldo_anterior">Sueldo Anterior:</label>
            <input type="number" name="sueldo_anterior" id="sueldo_anterior" readonly>

            <label for="nuevo_puesto">Nuevo Puesto:</label>
            <input type="text" name="nuevo_puesto" required>

            <label for="sueldo_nuevo">Nuevo Sueldo:</label>
            <input type="number" name="sueldo_nuevo" step="any" required>

            <label for="motivo">Motivo del Cambio:</label>
            <textarea name="motivo" required></textarea>

            <label for="fecha_cambio">Fecha de Cambio:</label>
            <input type="date" name="fecha_cambio" required>

            <button type="submit">Registrar Cambio</button>
        </form>
    </div>

    <!-- Script para autocompletar datos -->
    <script>
    document.querySelector('select[name="id_usuario"]').addEventListener('change', function () {
        const id_usuario = this.value;

        if (id_usuario !== "") {
            fetch(`obtener_datos_usuario.php?id_usuario=${id_usuario}`)
                .then(res => res.json())
                .then(data => {
                    document.getElementById('puesto_anterior').value = data.puesto_anterior || '';
                    document.getElementById('sueldo_anterior').value = data.sueldo_anterior || '';
                })
                .catch(error => {
                    console.error('Error al obtener datos:', error);
                    document.getElementById('puesto_anterior').value = '';
                    document.getElementById('sueldo_anterior').value = '';
                });
        } else {
            document.getElementById('puesto_anterior').value = '';
            document.getElementById('sueldo_anterior').value = '';
        }
    });
    </script>
</body>
</html>
