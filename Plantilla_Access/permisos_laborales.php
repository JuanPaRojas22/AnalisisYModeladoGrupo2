<?php
ob_start(); // Inicia el búfer de salida
// Conexión a la base de datos
session_start();
include 'conexion.php';
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">
    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />

    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">
    <title>Registrar Solicitud de Permiso</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado Permisos Laborales</title>

</head>

<body>
    <section class="container" id="container">
    <a href="VerPlanilla.php" class="button"><i class="bi bi-arrow-return-left"></i>
    </a>
        <h1>Listado de Permisos Laborales</h1>

        <div class="button-container">
            <button class="btn" onclick="abrirModal('modal3')">
                <i class="bi bi-journal-plus"> Solicitar Permiso</i>
            </button>

            <button id="ejecutar_approbar" class="btn">
                <a href="procesarPermiso.php">
                    <i class="bi bi-card-checklist"> Aprovar Solicitud</i>
                </a>
            </button>
            <button class="btn" onclick="abrirModal('modal2')">
                <i class="bi bi-file-earmark-medical"></i> Detalles Permisos
            </button>
        </div>

        <!-- Modal 2 - Detalles de Permisos -->
        <!-- Modal -->
        <div id="modal2" class="modal">
            <div class="modal-contenido">
                <span class="close" onclick="cerrarModal('modal2')">&times;</span>
                <h3 style="text-align: center; color: black; font-weight: bold;">Historial de Permisos</h3>
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
                    $sql = "SELECT u.nombre as nombre,u.apellido as apellido, h.FechaInicio as FechaInicio, h.FechaFin as FechaFin, h.DiasTomados as DiasTomados, h.Razon as Razon, h.Estado as Estado
                        FROM historial_permisos h
                        JOIN usuario u ON h.id_usuario = u.id_usuario
                        WHERE h.id_usuario = ?";


                    $stmt = $conn->prepare($sql);

                    if (!$stmt) {
                        die("Error en la preparación de la consulta: " . $conn->error);
                    }

                    $stmt->bind_param("i", $id_usuario); // i = integer (entero)
                    
                    $stmt->execute();
                    $resultado = $stmt->get_result(); // Obtener el resultado de la ejecución
                    
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
                        echo "<tr><td colspan='7'>No hay datos disponibles</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>

        <div id="modal3" class="modal">
    <div class="modal-content">
        <span class="close" onclick="cerrarModal('modal3')">&times;</span>
        <h3>Solicitar Permiso</h3>
        <form action="permisos_laborales.php" method="POST">
            <input type="hidden" name="accion" value="registrar">
            <input type="hidden" name="id_usuario" value="1">
            <label for="tipo_permiso">Tipo de Permiso:</label>
            <input type="text" name="tipo_permiso" required><br>
            <label for="fecha_inicio">Fecha Inicio:</label>
            <input type="date" name="fecha_inicio" required><br>
            <label for="fecha_fin">Fecha Fin:</label>
            <input type="date" name="fecha_fin" required><br>
            <label for="dias_permiso">Días de Permiso:</label>
            <input type="number" name="dias_permiso" required><br>
            <label for="motivo">Motivo:</label>
            <textarea name="motivo" required></textarea><br>
            <button type="submit" class="btn">Guardar Permiso</button>
        </form>
    </div>
</div>



        <?php
        // Incluir la conexión a la base de datos
        include 'conexion.php';

        // Consulta SQL para obtener los permisos laborales con sus estados
        $sql = "SELECT DISTINCT p.id_permiso as permiso, p.id_usuario as id, p.tipo_permiso, p.fecha_inicio, p.fecha_fin, p.motivo, e.descripcion AS estado, u.nombre AS usuario
        FROM permisos_laborales p
        JOIN estado_permiso e ON p.id_estado = e.id_estado
        JOIN usuario u ON p.id_usuario = u.id_usuario
        WHERE p.id_usuario = ?"; // Filtra por el id_usuario del usuario logueado
        
        // Prepara la consulta
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario); // Usamos el ID del usuario de la sesión
        $stmt->execute();

        // Ejecutar la consulta
        $result = $stmt->get_result();


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
                        echo "<td>
                        <form action='permisos_laborales.php' method='POST'>
                        <input type='hidden' name='accion' value='eliminar'>
                        <input type='hidden' name='id_permiso' value='" . $row['permiso'] . "'>
                        <button type='submit' class='btn-danger'><i class='bi bi-trash'></i></button>
                        </form></td>";
                        echo "</tr>";

                    }
                } else {
                    echo "<tr><td colspan='6' >No hay permisos registrados.</td></tr>";
                }
                ?>
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

    // Función para cerrar el modal cuando se hace clic fuera de él
    window.onclick = function(event) {
        var modals = document.querySelectorAll('.modal');
        modals.forEach(function(modal) {
            // Verifica si el clic fue fuera del modal y lo cierra
            if (event.target === modal) {
                modal.style.display = "none";
            }
        });
    }
</script>

</body>
<style>
    
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;
    }




    .container {
        
        width: 100%;
        margin-top: 200px;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
    }

    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 50px;
        font-weight: bold;
    }

    .btn {
        display: inline-block;
        background-color: #c9aa5f;
        color: white;
        padding: 10px 20px;
        font-size: 25px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: background-color 0.3s;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
    }

    

    .btn:hover {
        background-color: #c9aa5f;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        border-radius: 8px;
        overflow: hidden;
        font-weight: bold;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);

    }


    #ejecutar_approbar a {
        text-decoration: none;
        /* Eliminar subrayado del enlace */
        color: inherit;
        /* El enlace tomará el color del ícono */
    }

    th,
    td {
        padding: 12px;
        text-align: center;
        font-size: 16px;
        color: #555;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #c9aa5f;
        color: #fff;
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
        font-weight: bold;
        color: black;
        text-align: center;

    }

    .modal-ajuste {
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
    .btn-danger{
        font-size: 30px;
        border-radius: 10px;
        align-items: center;
    }
    .button {
        display: inline-block;
        background-color: #c9aa5f;
        color: white;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        margin-bottom: 20px;
        transition: background-color 0.3s;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
    }

    .modal-contenido {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        width: 70%;
        position: relative;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
    }

    label {
        display: block;
        margin-top: 10px;
        font-weight: bold;
        color: black;

    }

    input[type="text"],
    input[type="date"],
    input[type="number"],

    textarea {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
        font-weight: bold;

        text-align: center;

    }

    textarea {
        height: 80px;
        resize: none;
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
</style>


<style>
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;
    }


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



</html>