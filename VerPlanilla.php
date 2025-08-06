<?php
session_start();
require "template.php";

// Verificar sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

$rol_usuario = $_SESSION['id_rol'] ?? null;
$id_usuario = $_SESSION['id_usuario'];
$id_departamento = $_SESSION['id_departamento'] ?? null;

// Parámetros de paginación
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $items_per_page;

// Conexión (igual que antes)...

// Obtener id_departamento para rol 2 (si no lo tienes ya)
if ($rol_usuario == 2 && $id_departamento === null) {
    $stmt_depto = $conn->prepare("SELECT id_departamento FROM Usuario WHERE id_usuario = ?");
    $stmt_depto->bind_param("i", $id_usuario);
    $stmt_depto->execute();
    $res_depto = $stmt_depto->get_result();
    if ($row = $res_depto->fetch_assoc()) {
        $id_departamento = $row['id_departamento'];
    }
    $stmt_depto->close();
}

// Consulta para contar total registros
$sql_count = "SELECT COUNT(DISTINCT u.id_usuario) AS total FROM Usuario u ";
$where = "";
$params = [];
$types = "";

if ($rol_usuario == 3) {
    $where = " WHERE u.id_usuario = ?";
    $params[] = $id_usuario;
    $types .= "i";
} elseif ($rol_usuario == 1 && $id_departamento !== null) {
    $where = " WHERE u.id_departamento = ?";
    $params[] = $id_departamento;
    $types .= "i";
}

$sql_count .= $where;
$stmt_count = $conn->prepare($sql_count);
if (!$stmt_count) {
    die("Error en count: " . $conn->error);
}
if (count($params) > 0) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$res_count = $stmt_count->get_result();
$total_rows = $res_count->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $items_per_page);
$stmt_count->close();

// Consulta principal
$sql_base = "SELECT 
    u.nombre,
    u.apellido,
    u.correo_electronico,
    u.id_ocupacion,
    MAX(o.nombre_ocupacion) AS nombre_ocupacion,
    p.total_deducciones,
    p.salario_base, 
    p.salario_neto, 
    COALESCE(GROUP_CONCAT(DISTINCT CONCAT('- ', d.razon) SEPARATOR '\n'), 'Sin deducciones') AS nombre_deduccion,
    COALESCE(GROUP_CONCAT(DISTINCT CONCAT('- ', b.razon) SEPARATOR '\n'), 'Sin bonos') AS nombre_bono,
    COALESCE(GROUP_CONCAT(DISTINCT te.descripcion SEPARATOR ', '), 'Sin clasificación') AS clasificaciones
FROM planilla p
JOIN Usuario u ON p.id_usuario = u.id_usuario
LEFT JOIN deducciones d ON p.id_usuario = d.id_usuario  
LEFT JOIN bonos b ON p.id_usuario = b.id_usuario
LEFT JOIN ocupaciones o ON o.id_ocupacion = u.id_ocupacion
LEFT JOIN empleado_tipo_empleado ete ON p.id_usuario = ete.id_empleado
LEFT JOIN tipo_empleado te ON ete.id_tipo_empleado = te.id_tipo_empleado ";

$sql = $sql_base . $where . " GROUP BY 
    u.id_usuario, 
    u.nombre, 
    u.apellido, 
    u.correo_electronico, 
    u.id_ocupacion,
    p.total_deducciones,
    p.salario_base,
    p.salario_neto
ORDER BY u.nombre DESC
LIMIT $items_per_page OFFSET $offset";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error en prepare: " . $conn->error);
}
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

            <html lang="es">

            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Listado Planilla</title>
                <style>
                    body {
                        font-family: 'Ruda', sans-serif;
                        background-color: #f7f7f7;
                        /* Blanco cremoso */
                        margin: 0;
                        padding: 0;

                    }

                    .container {
                        
                        width: 75%;
                        /* Aumentar el tamaño del contenedor */
                        margin: 50px auto;
                        /* Centrar el contenedor */
                        
                        padding: 20px;
                        background-color: #fff;
                        border-radius: 12px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                        /* Sombra alrededor del contenedor */
                    }

                    h1 {
                        text-align: center;
                        color: #333;
                        margin-bottom: 50px;
                        margin-right: 2%;
                        font-weight: bold;

                    }

                    h3 {
                        text-align: center;
                        color: black;
                        margin-bottom: 50px;
                        margin-right: 10%;
                        font-weight: bold;
                    }

                    .btn {
                        display: inline-block;
                        background-color: #0C536C;
                        color: white;
                        padding: 12px 20px;
                        font-size: 16px;
                        font-weight: bold;
                        text-decoration: none;
                        border-radius: 5px;
                        margin-bottom: 20px;
                        transition: background-color 0.3s;
                        cursor: pointer;
                        border: none;
                    }

                    .btn:hover {
                        background-color: #0C536C;
                    }


                    .btn:active {
                        background-color: #0C536C;
                        box-shadow: 0 4px 10px rgb(254, 254, 254);

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
                        background-color: #f7f7f7;
                    }

                    td {
                        background-color: #f7f7f7;
                    }

                    .no-records {
                        text-align: center;
                        font-style: italic;
                        color: #888;
                    }

                    /* Estilos del fondo del modal */
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
                        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);

                    }

                    /* Contenido del modal */
                    .modal-content {
                        background-color: white;
                        padding: 20px;
                        border-radius: 10px;
                        width: 300px;
                        text-align: center;
                        margin-bottom: 5%;

                    }

                    /* Botón de cerrar */
                    .close {
                        position: absolute;
                        top: 10px;
                        right: 20px;
                        font-size: 25px;
                        cursor: pointer;
                    }

                    /* Botones dentro del modal */
                    .modal-content a {
                        display: block;
                        margin: 10px 0;
                        padding: 10px;
                        text-decoration: none;
                        color: white;
                        background-color: gray;
                        border-radius: 5px;
                        background-color: #147964;
                    }

                    .modal-content a:hover {
                        background-color: darkgray;
                    }

                    /* Estilos para los botones alineados */
                    .button-container {
                        display: flex;
                        justify-content: space-between;
                        /* Distribuye el espacio entre los botones */
                        width: 100%;

                    }
                </style>
            </head>

            <body>
                <div class="container">
                    <h1>Listado de Planilla</h1>
                <?php if ($rol_usuario != 3): ?>
                    <div class="button-container">
                        <!-- Botón para abrir el primer modal (3 botones) -->
                        <button class="btn" onclick="abrirModal('modal1')">
                            <i class="bi bi-gear"></i>
                        </button>


                        <button id="ejecutar_pago" class="btn">
                            <i class="bi bi-cash-stack"></i> Ejecutar Pagos
                        </button>

                        <?php endif; ?>

                        <!-- Botón para abrir el segundo modal (resto de los botones) -->
                        <button class="btn" onclick="abrirModal('modal2')">
                            <i class="bi bi-journal-medical"></i>
                        </button>
                        

                        <?php if (isset($mensaje)): ?>
                            <div class="alert alert-success mt-3">
                                <?php echo $mensaje; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                    <input type="text" id="searchInput" class="custom-input" onkeyup="searchTable()"
                        placeholder="Buscar Empleado...">
               



                    <!-- Modal 1 con 3 botones -->
                    <div id="modal1" class="modal">
                        <div class="modal-content">
                            <span class="close" onclick="cerrarModal('modal1')">&times;</span>
                            <h3>Ajustes de Planilla</h3>
                            <a href="registrar_horas_extras.php">Registrar Horas extras</a>
                            <a href="calcular_aguinaldo.php">Calcular Aguinaldos</a>
                            <a href="RegistroPlanilla.php">Registrar Planilla</a>
                            <a href="permisos_laborales.php">Permisos Laborales</a>
                            <a href="aplicarBono.php">Aplicar Bono</a>
                            <a href="actualizarSalarios.php">Ajustar Salario</a>
                            <a href="aplicarRetenciones.php">Aplicar Deducción</a>
                            <a href="registrar_cambio_puesto.php">Ajustar Puesto</a>
                        </div>
                    </div>

                    <!-- Modal 2 con el resto de los botones -->
                    <div id="modal2" class="modal">
                        <div class="modal-content">
                            <span class="close" onclick="cerrarModal('modal2')">&times;</span>
                            <h3>Detalles Planilla</h3>
                            <a href="Verdeducciones.php">Ver Deducciones</a>
                            <a href="ver_historial_cambios.php">Ver Historial de Puestos</a>
                            <a href="verBono.php">Ver Bonos</a>
                            <a href="Filtro_horas_extras.php">Horas Extras</a>
                            <a href="Filtro_clasificacion_empleado.php">Ver Clasificaciones</a>
                        </div>
                    </div>
                </div>

                <div id="mensaje_alerta"></div>

                <div class="container">

                    <table>
                        <thead>
                            <tr>

                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Correo</th>
                                <th>Cargo</th>
                                <th>Salario base</th>
                                <th>Bonos</th>
                                <th>Deduccion</th>
                                <th style="text-align: center;">Total Deduccion<br>Quincenal</br>
                                <th>Salario neto Quincenal</th>
                                <th>Clasificacion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Mostrar los resultados de la consulta
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>
                                <td>" . $row['nombre'] . "</td>
                                <td>" . $row['apellido'] . "</td>
                                <td>" . $row['correo_electronico'] . "</td>
                                <td>" . $row['nombre_ocupacion'] . "</td>
                                <td>" . $row['salario_base'] . "</td>
                                <td>" . nl2br($row['nombre_bono']) . "</td>
                                <td>" . nl2br($row['nombre_deduccion']) . "</td>
                                <td style='text-align: center;'>" . $row['total_deducciones'] . "</td>
                                <td>" . $row['salario_neto'] . "</td>
                                <td>" . nl2br($row['clasificaciones']) . "</td>
                              </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' class='no-records'>No se encontraron registros.</td></tr>";
                            }
                            ?>
                       
                        </tbody>
                    </table>
                      <!-- Paginación -->
                    <?php if ($total_pages > 1): ?>
    <div align="right">
        <nav aria-label="Page navigation">
            <ul class="pagination" style="display: inline-block; padding-left: 0; margin-bottom: 0;">
                <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>" style="display: inline; margin-right: 5px;">
                    <a class="page-link" href="?page=<?= $page - 1 ?>">Anterior</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $page ? 'active' : '' ?>" style="display: inline; margin-right: 5px;">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>" style="display: inline;">
                    <a class="page-link" href="?page=<?= $page + 1 ?>">Siguiente</a>
                </li>
            </ul>
        </nav>
    </div>
<?php endif; ?>


                </div>
        </section>

        <script>
            function searchTable() {
                let input = document.getElementById('searchInput');
                let filter = input.value.toLowerCase();
                let table = document.querySelector('table');
                let rows = table.getElementsByTagName('tr');

                // Iterar sobre las filas de la tabla (saltando la primera fila que es el encabezado)
                for (let i = 1; i < rows.length; i++) {
                    let cells = rows[i].getElementsByTagName('td');
                    let rowText = '';
                    // Concatenar el texto de todas las celdas en cada fila
                    for (let j = 0; j < cells.length; j++) {
                        rowText += cells[j].textContent.toLowerCase() + ' ';
                    }

                    // Verificar si el texto de la fila coincide con el término de búsqueda
                    if (rowText.indexOf(filter) > -1) {
                        rows[i].style.display = '';
                    } else {
                        rows[i].style.display = 'none';
                    }
                }
            }
        </script>
</body>


</html>

<script>
    // Función para abrir el modal
    function abrirModal(modalId) {
        document.getElementById(modalId).style.display = 'flex';
    }

    // Función para cerrar el modal
    function cerrarModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }
</script>

<script>
    $(document).ready(function () {
        $('#ejecutar_pago').click(function () {
            // Enviar la solicitud AJAX al archivo PHP
            $.ajax({
                url: 'pago_planilla.php',
                type: 'POST',
                data: { ejecutar_pago: true },
                dataType: 'json',
                success: function (response) {
                    // Mostrar el mensaje en el HTML
                    if (response.mensaje) {
                        $('#mensaje_alerta').html('<div class="alert alert-success text-center">' + response.mensaje + '</div>');
                    } else {
                        $('#mensaje_alerta').html('<div class="alert alert-danger text-center">Hubo un error al ejecutar los pagos.</div>');
                    }
                },
                error: function () {
                    $('#mensaje_alerta').html('<div class="alert alert-danger text-center">Ya se ha generado un pago para esta Quincena.</div>');
                }
            });
        });
    });
</script>



<script>


    // Cerrar el modal si se hace clic fuera de él
    window.onclick = function (event) {
        var modal = document.getElementById("modalHorasExtras");
        if (event.target == modal) {
            cerrarModal("modalHorasExtras");
        }
    }

</script>



<?php
// Cerrar la conexión
$conn->close();
ob_end_flush();
?>
