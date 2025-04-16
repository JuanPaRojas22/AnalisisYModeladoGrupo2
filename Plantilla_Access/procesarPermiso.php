<?php
ob_start(); // Inicia el búfer de salida

session_start();
require 'conexion.php'; // Asegúrate de incluir la conexión a la BD
require 'template.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recibe el valor de id_solicitud que puede ser Aprobado o Rechazado según el botón que se presione
    if (isset($_POST['id_solicitud']) && !empty($_POST['id_solicitud'])) {
        $id_solicitud = $_POST['id_solicitud'];
    } else {
        die("Error: ID de solicitud no proporcionado.");
    }

    // Aquí revisas si el estado es aprobado o rechazado
    if (isset($_POST['accion']) && $_POST['accion'] == 'aprobar') {
        $id_estado = 1; // Aprobado
    } elseif (isset($_POST['accion']) && $_POST['accion'] == 'rechazar') {
        $id_estado = 3; // Rechazado
    } else {
        die("Error: Acción no válida.");
    }

    // Actualizar el estado de la solicitud
    $sql = "UPDATE permisos_laborales SET id_estado = ? WHERE id_permiso = ? AND id_estado = 2";  // Usamos 'id_permiso' aquí
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_estado, $id_solicitud);

    if ($stmt->execute()) {
        // Usa la variable de sesión para mostrar el mensaje en la página siguiente
        $_SESSION['mensaje'] = "Solicitud procesada correctamente.";
    } else {
        $_SESSION['mensaje'] = "Error al procesar la solicitud: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    // Redirige a la lista de solicitudes
    header("Location: procesarPermiso.php");
    exit();
}

ob_end_flush(); // Libera el búfer y envía la salida al navegador
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="assets/css/zabuto_calendar.css">
    <link rel="stylesheet" type="text/css" href="assets/js/gritter/css/jquery.gritter.css" />
    <link rel="stylesheet" type="text/css" href="assets/lineicons/style.css">

    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">
    <title>Registrar Solicitud de Permiso</title>


    <style>
        /* Estilos para el modal */
        .modal {
            display: none;
            /* Inicialmente oculto */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
   
    margin: 50px auto; /* Centering the container */
    padding: 15px;
   
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Light shadow */
    display: flex;
    flex-direction: column;
    align-items: center;
}

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 50px;
            font-weight: bold;
            
        }

        .btn-aprove {
            display: inline-block;
            background-color: #147964;
            color: white;
            padding: 10px 20px;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }

        .btn-aprove:hover {
            background-color: #147964;
        }

        .btn-decline {
            display: inline-block;
            background-color: rgb(206, 69, 69);
            color: white;
            padding: 10px 20px;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 10px;
            transition: background-color 0.3s;
        }

        .btn-decline:hover {
            background-color: rgb(206, 69, 69);
        }


        table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 12px; /* Reducir el tamaño de la fuente */
}

th, td {
    padding: 8px; /* Reducir el espaciado de las celdas */
    text-align: center;
    font-size: 12px; /* Reducir el tamaño de la fuente */
    color: #555;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #116B67;
    color: white;
}
th:first-child {
    border-radius: 8px 0 0 0; /* Redondear la esquina superior izquierda */
}
th:last-child {
    border-radius: 0 8px 0 0; /* Redondear la esquina superior derecha */
}

tr:hover {
    background-color: #f1f1f1;
}
        .no-records {
            text-align: center;
            font-style: italic;
            color: #888;
        }

        /* Modal Styles */
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
            width: 300px;
            text-align: center;
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

        .button {
        display: inline-block;
        background-color: #0B4F6C;
        color: white;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);

        transition: background-color 0.3s;
    }
    </style>
</head>

<body>
    <div class="container">
        <a href="permisos_laborales.php" class="button"><i class="bi bi-arrow-return-left"></i>
        </a>
        <h1>Listado de Permisos Laborales</h1>

        <?php
        // Incluir la conexión a la base de datos
        include 'conexion.php';

        // Consulta SQL para obtener los permisos laborales con sus estados
        $sql = "SELECT DISTINCT p.id_permiso as permiso,p.id_usuario as id,p.tipo_permiso, p.fecha_inicio, p.fecha_fin, p.motivo, e.descripcion AS estado, u.nombre AS usuario
        FROM permisos_laborales p
        JOIN estado_permiso e ON p.id_estado = e.id_estado
        JOIN usuario u ON p.id_usuario = u.id_usuario
        WHERE e.id_estado = 2 ";


        // Ejecutar la consulta
        $result = $conn->query($sql);

        // Comprobar si hubo error en la consulta
        if (!$result) {
            die("Error en la consulta SQL: " . $conn->error);
        }
        ?>

        <!-- Mostrar Tabla de Permisos -->
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
                // Verificar si hay datos
                if ($result->num_rows > 0) {
                    // Recorrer los resultados y mostrar cada fila
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['usuario']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['fecha_inicio']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['fecha_fin']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['tipo_permiso']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['motivo']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['estado']) . "</td>";
                        echo "<td>";

                        // Verificar si el estado es 'Pendiente' para permitir la aprobación
                        // Verificar si el estado es 'Pendiente' para permitir la aprobación
                        if ($row['estado'] == 'Pendiente') {
                            echo '<form method="POST" action="procesarPermiso.php">';
                            echo '<input type="hidden" name="id_solicitud" value="' . $row['permiso'] . '">';
                            echo '<input type="hidden" name="accion" value="aprobar">';
                            echo '<button type="submit" class="btn-aprove"><i class="bi bi-check-circle-fill"></i></button>';
                            echo '</form>';

                            echo '<form method="POST" action="procesarPermiso.php" style="display:inline;">';
                            echo '<input type="hidden" name="id_solicitud" value="' . $row['permiso'] . '">';
                            echo '<input type="hidden" name="accion" value="rechazar">';
                            echo '<button type="submit" class="btn-decline"><i class="bi bi-x-square-fill"></i></button>';
                            echo '</form>';
                        } else {
                            echo 'No Disponible';
                        }

                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No hay permisos registrados.</td></tr>";
                }
                ?>
        </table>
    </div>

</body>

</html>