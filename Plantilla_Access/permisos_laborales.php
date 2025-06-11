<?php
ob_start(); // Inicia el búfer de salida
// Conexión a la base de datos

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


$id_usuario = $_SESSION['id_usuario'];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['accion']) && $_POST['accion'] == 'registrar') {
        $id_usuario = $_SESSION['id_usuario'] ?? '';
        $tipo_permiso = $_POST['tipo_permiso'] ?? '';
        $fecha_inicio = $_POST['fecha_inicio'] ?? '';
        $fecha_fin = $_POST['fecha_fin'] ?? '';
        $dias_permiso = $_POST['dias_permiso'] ?? '';
        $motivo = $_POST['motivo'] ?? '';

        $fechacreacion = date('Y-m-d');

        $sql = "INSERT INTO permisos_laborales 
                (id_usuario, tipo_permiso, fecha_inicio, fecha_fin, dias_permiso, motivo, fechacreacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isssiss", $id_usuario, $tipo_permiso, $fecha_inicio, $fecha_fin, $dias_permiso, $motivo, $fechacreacion);
            if ($stmt->execute()) {
                // Redirigir para evitar reenvío del formulario
                header("Location: permisos_laborales.php");
                exit();
            } else {
                echo "<p>Error al registrar la solicitud: " . $stmt->error . "</p>";
            }
            $stmt->close();
        } else {
            echo "<p>Error en la preparación de la consulta.</p>";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['accion']) && $_POST['accion'] == 'eliminar') {
        $id_permiso = $_POST['id_permiso'] ?? '';

        if (!empty($id_permiso)) {
            // Consulta SQL para eliminar el permiso
            $sql = "DELETE FROM permisos_laborales WHERE id_permiso = ?";

            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $id_permiso); // Usamos el ID del permiso a eliminar
                if ($stmt->execute()) {
                    // Redirigir a la lista de permisos después de la eliminación
                    header("Location: permisos_laborales.php");
                    exit();
                } else {
                    echo "<p>Error al eliminar el permiso: " . $stmt->error . "</p>";
                }
                $stmt->close();
            } else {
                echo "<p>Error en la preparación de la consulta.</p>";
            }
        }
    }
}

ob_end_flush(); // Libera el búfer y envía la salida al navegador
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <title>Listado Permisos Laborales</title>

    <style>
        body {
            font-family: 'Ruda', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 70%;

            margin: 50px auto;
            /* Centering the container */
            padding: 15px;

            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            /* Light shadow */
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        h1 {
            text-align: center;
            color: #333;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .btn {
            display: inline-block;
            background-color: #147665;
            color: white;
            padding: 10px 20px;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background-color 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
        }

        .btn:hover {
            background-color: #147665;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
            /* Reducir el tamaño de la fuente */
        }

        th,
        td {
            padding: 8px;
            /* Reducir el espaciado de las celdas */
            text-align: center;
            font-size: 12px;
            /* Reducir el tamaño de la fuente */
            color: #555;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #116B67;
            color: white;
        }

        th:first-child {
            border-radius: 8px 0 0 0;
            /* Redondear la esquina superior izquierda */
        }

        th:last-child {
            border-radius: 0 8px 0 0;
            /* Redondear la esquina superior derecha */
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .no-records {
            text-align: center;
            font-style: italic;
            color: #888;
        }

        button {
            background-color: #0B4F6C;
            color: white;
            padding: 10px 20px;
            font-size: 12px;
            border-radius: 5px;
            transition: background-color 0.3s;
            margin-bottom: 20px;
        }

        button:hover {
            background-color: #147665;
        }

        button a {
            text-decoration: none;
            color: inherit;
        }

        button:active {
            background-color: #147665;
            box-shadow: 0 4px 10px rgb(254, 254, 254);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 500px;
            text-align: center;
            font-weight: bold;
            color: black;
            margin-top: 50px;
        }

        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 25px;
            cursor: pointer;
        }

        .modal-content a {
            display: block;
            margin: 10px 0;
            padding: 10px;
            text-decoration: none;
            color: white;
            background-color: #c9aa5f;
            border-radius: 5px;
        }

        .modal-content a:hover {
            background-color: darkgray;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .modal-content form {
            display: flex;
            flex-direction: column;
            /* Organiza los elementos en columnas */
            gap: 15px;
            /* Espaciado entre los elementos */
        }

        /* Estilo para las etiquetas */
        .modal-content label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        /* Estilo para los campos de entrada */
        .modal-content input,
        .modal-content textarea {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 14px;
            width: 100%;
            /* Asegura que los campos ocupen todo el ancho disponible */
        }
    </style>

</head>

<body>

    <section class="container">
        <a href="VerPlanilla.php" class="button"
            style="background-color: #0E5D6A; color: white; padding: 12px 25px; font-size: 14px; border-radius: 8px; border: none; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); transition: background-color 0.3s, transform 0.3s; margin-bottom: 20px; position: relative; right:100px; left: 0px; margin-left: 20px;">
            <i class="bi bi-arrow-return-left"></i>
        </a>

        <style>
            .button:hover {
                background-color: #0E5D6A;
                /* Cambio de color al pasar el ratón */
                transform: translateY(-2px);
                /* Efecto de elevación al pasar el ratón */
            }
        </style>

        <h1>Listado de Permisos Laborales</h1>

        <div class="button-container">
            <button class="btn" onclick="abrirModal('modal3')">
                <i class="bi bi-journal-plus"> Solicitar Permiso</i>
            </button>
            <button id="ejecutar_approbar" class="btn">
                <a href="procesarPermiso.php">
                    <i class="bi bi-card-checklist"> Aprobar Solicitud</i>
                </a>
            </button>
            <button class="btn" onclick="abrirModal('modal2')">
                <i class="bi bi-file-earmark-medical"></i> Detalles Permisos
            </button>
        </div>

        <!-- Modal 2 - Detalles de Permisos -->
        <div id="modal2" class="modal">
            <div class="modal-content">
                <span class="close" onclick="cerrarModal('modal2')">&times;</span>
                <h3>Historial de Permisos</h3>
                <table>
                    <tr>
                        <th>Usuario</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Días Tomados</th>
                        <th>Razón</th>
                        <th>Estado</th>
                    </tr>
                    <?php
                    $sql = "SELECT u.nombre, u.apellido, h.FechaInicio, h.FechaFin, h.DiasTomados, h.Razon, h.Estado
                        FROM historial_permisos h
                        JOIN usuario u ON h.id_usuario = u.id_usuario
                        WHERE h.id_usuario = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id_usuario);
                    $stmt->execute();
                    $resultado = $stmt->get_result();
                    if ($resultado->num_rows > 0) {
                        while ($fila = $resultado->fetch_assoc()) {
                            echo "<tr>
                            <td>{$fila['nombre']} {$fila['apellido']}</td>
                            <td>{$fila['FechaInicio']}</td>
                            <td>{$fila['FechaFin']}</td>
                            <td>{$fila['DiasTomados']}</td>
                            <td>{$fila['Razon']}</td>
                            <td>{$fila['Estado']}</td>
                            
                        </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No hay datos disponibles</td></tr>";
                    }
                    ?>


                </table>

                <form action="reporte_permisos.php" method="get" target="_blank">
                    <input type="hidden" name="id_usuario" value="<?= $id_usuario ?>">
                    <button type="submit">Descargar PDF</button>
                </form>
            </div>
        </div>

        <!-- Modal 3 - Solicitar Permiso -->
        <div id="modal3" class="modal">
            <div class="modal-content">
                <span class="close" onclick="cerrarModal('modal3')">&times;</span>
                <h3>Solicitar Permiso</h3>
                <form action="permisos_laborales.php" method="POST">
                    <input type="hidden" name="accion" value="registrar">
                    <input type="hidden" name="id_usuario" value="1">

                    <label for="tipo_permiso">Tipo de Permiso:</label>
                    <input type="text" name="tipo_permiso" required>

                    <label for="fecha_inicio">Fecha Inicio:</label>
                    <input type="date" name="fecha_inicio" required>

                    <label for="fecha_fin">Fecha Fin:</label>
                    <input type="date" name="fecha_fin" required>

                    <label for="dias_permiso">Días de Permiso:</label>
                    <input type="number" name="dias_permiso" required>

                    <label for="motivo">Motivo:</label>
                    <textarea name="motivo" required></textarea>

                    <button type="submit" class="btn">Guardar Permiso</button>
                </form>
            </div>
        </div>

        <!-- Tabla de Permisos -->
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Tipo de Permiso</th>
                    <th>Motivo</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT p.id_permiso, u.nombre, u.apellido, p.fecha_inicio, p.fecha_fin, p.tipo_permiso, p.motivo, e.descripcion AS estado
                    FROM permisos_laborales p
                    JOIN estado_permiso e ON p.id_estado = e.id_estado
                    JOIN usuario u ON p.id_usuario = u.id_usuario
                    WHERE p.id_usuario = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id_usuario);
                $stmt->execute();
                $resultado = $stmt->get_result();
                if ($resultado->num_rows > 0) {
                    while ($row = $resultado->fetch_assoc()) {
                        echo "<tr>
                        <td>{$row['nombre']} {$row['apellido']}</td>
                        <td>{$row['fecha_inicio']}</td>
                        <td>{$row['fecha_fin']}</td>
                        <td>{$row['tipo_permiso']}</td>
                        <td>{$row['motivo']}</td>
                        <td>{$row['estado']}</td>
                        <td>
                            <form action='permisos_laborales.php' method='POST'>
                                <input type='hidden' name='accion' value='eliminar'>
                                <input type='hidden' name='id_permiso' value='{$row['id_permiso']}'>
                                <button type='submit' class='btn-danger'><i class='bi bi-trash'></i></button>
                            </form>
                        </td>
                    </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No hay permisos registrados.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </section>

    <script>
        // Función para cerrar el modal
        function cerrarModal(modalId) {
            var modal = document.getElementById(modalId);
            modal.style.display = "none";
        }

        // Función para abrir el modal
        function abrirModal(id) {
            document.getElementById(id).style.display = "flex";
        }

        // Cerrar el modal si se hace clic fuera de él
        window.onclick = function (event) {
            var modals = document.querySelectorAll('.modal');
            modals.forEach(function (modal) {
                if (event.target === modal) {
                    modal.style.display = "none";
                }
            });
        }
    </script>

</body>

</html>