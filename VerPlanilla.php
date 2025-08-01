<?php
ob_start(); // Inicia el búfer de salida para evitar que se envíen cabeceras prematuramente

session_start();
require "template.php";
// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}




?>


<!DOCTYPE html>
<html lang="en">



<body>

    <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
    <!--main content start-->
    <section id="main-content">
        <section class="wrapper site-min-height">

            <!-- /MAIN CONTENT -->
            <?php
            // Verificar si el usuario está logueado
            // Conexión a la base de datos
// Parámetros de conexión
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

// Ruta al certificado CA para validar SSL
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

// Inicializamos mysqli
$conn = mysqli_init();

// Configuramos SSL
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);


// Intentamos conectar usando SSL (con la bandera MYSQLI_CLIENT_SSL)
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecemos el charset
mysqli_set_charset($conn, "utf8mb4");

//echo "Conectado correctamente con SSL.";


            // Consulta para obtener el historial de cambios
            $sql = "SELECT 
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
        LEFT JOIN tipo_empleado te ON ete.id_tipo_empleado = te.id_tipo_empleado
        GROUP BY 
            u.nombre, 
            u.apellido, 
            u.correo_electronico, 
            u.id_ocupacion,
            p.total_deducciones,
            p.salario_base,
            p.salario_neto
        ORDER BY u.nombre DESC";

            /*

            - Se agregó la relación con empleado_tipo_empleado (LEFT JOIN empleado_tipo_empleado ete ON p.id_usuario = ete.id_empleado), para obtener las clasificaciones de los empleados.

            - Se agregó la relación con tipo_empleado (LEFT JOIN tipo_empleado te ON ete.id_tipo_empleado = te.id_tipo_empleado), para obtener la descripción de cada clasificación.

            - Uso de GROUP_CONCAT(DISTINCT te.descripcion SEPARATOR ', '):

            - Une todas las clasificaciones en una sola columna, separadas por comas.

            - Si un empleado no tiene clasificación, muestra 'Sin clasificación'.
            
            */

            $result = $conn->query($sql);

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
                        width: 90%;
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

                    <div class="button-container">
                        <!-- Botón para abrir el primer modal (3 botones) -->
                        <button class="btn" onclick="abrirModal('modal1')">
                            <i class="bi bi-gear"></i>
                        </button>


                        <button id="ejecutar_pago" class="btn">
                            <i class="bi bi-cash-stack"></i> Ejecutar Pagos
                        </button>



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

<?php if ($total_pages > 1): ?>
    <nav aria-label="Page navigation" class="mt-4">
        <ul class="pagination justify-content-end" style="width: 80%; margin: auto; padding-right: 20px;">
            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page - 1 ?>">Anterior</a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                    <a class="page-link" href="?page=<?= $i ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                <a class="page-link" href="?page=<?= $page + 1 ?>">Siguiente</a>
            </li>
        </ul>
    </nav>
<?php endif; ?>

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
