<?php

session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
//Parámetros de conexión
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


// Verificar autenticación del usuario
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}
$rol = $_SESSION['id_rol'];
$id_usuario = $_SESSION['id_usuario'];
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Invitado';

// Incluir la plantilla
include 'template.php';
// Verificar si se han enviado los filtros
if (isset($_POST['filtrar'])) {
    $usuario = $_POST['usuario'];
    $departamento = $_POST['departamento'];


    // Mostrar valores de los filtros (para depuración)
    //var_dump($usuario);
    //var_dump($departamento);

    // Construir la consulta SQL con los filtros seleccionados
    $query = "SELECT u.nombre, d.Nombre, SUM(he.horas) AS total_horas_extras, SUM(he.monto_pago) AS monto_pago
    FROM historial_horas_extras he
    JOIN usuario u ON he.id_usuario = u.id_usuario
    JOIN departamento d ON u.id_departamento = d.id_departamento
    WHERE 1";  // Esto asegura que siempre haya una condición base

    // Si es un usuario normal, solo puede ver sus propias horas
    // Si el usuario es normal (rol = 3), forzar filtro por su ID
    if ($rol == 3) {
        $query .= " AND u.id_usuario = '$id_usuario'";
    } else {
        // Admin o master: aplicar filtros si vienen
        if (!empty($usuario)) {
            $query .= " AND u.id_usuario = '$usuario'";
        }
        if (!empty($departamento)) {
            $query .= " AND d.id_departamento = '$departamento'";
        }
    }


    $query .= " GROUP BY u.id_usuario, u.nombre, d.Nombre";

    // Muestra la consulta SQL generada para depuración
//echo "<pre>" . $query . "</pre>";

    // Ejecutar la consulta
    $result = mysqli_query($conn, $query);

    // Verificar si hay resultados
    if ($result && mysqli_num_rows($result) > 0) {
        $data = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
    } else {
        $data = null;
    }

}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filtrar Horas Extras</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        td,
        div {
            color: black !important;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Filtrar Horas Extras</h1>
        <?php if ($rol != 3): ?>
            <!-- Formulario de filtros -->
            <form action="Filtro_horas_extras.php" method="post" class="filter-form">
                <label for="usuario">Usuario:</label>
                <select name="usuario" id="usuario">
                    <option value="">Selecciona un Usuario</option>
                    <?php
                    // Aquí se llenan los usuarios desde la base de datos
                    $query = "SELECT id_usuario, nombre FROM usuario";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $row['id_usuario'] . "'>" . $row['nombre'] . "</option>";
                    }
                    ?>
                </select>

                <label for="departamento">Departamento:</label>
                <select name="departamento" id="departamento">
                    <option value="">Selecciona un Departamento</option>
                    <?php
                    // Aquí se llenan los departamentos desde la base de datos
                    $query = "SELECT id_departamento, Nombre FROM departamento";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . $row['id_departamento'] . "'>" . $row['Nombre'] . "</option>";
                    }
                    ?>
                </select>

                <div class="button-group">
                    <!-- Botón Filtrar -->
                    <a href="VerPlanilla.php" class="btn btn-secondary">
                        <i></i> Devolver
                    </a>
                    <button class="btn" type="submit" name="filtrar" id="btnFiltrar">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
            </form>
        <?php endif; ?>

        <?php if (!empty($data)): ?>
            <form action="reporte_horas_extra.php" method="post">
                <input type="hidden" name="usuario" value="<?php echo $usuario; ?>">
                <input type="hidden" name="departamento" value="<?php echo $departamento; ?>">
                <button class="btn" type="submit" name="exportar_pdf">
                    <i class="bi bi-file-earmark-arrow-down-fill"></i> Exportar PDF
                </button>
            </form>
        <?php endif; ?>
    </div>


    <!-- Mostrar los resultados -->
    <table>
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Departamento</th>
                <th>Total Horas Extras</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Mostrar los resultados de las horas extras
            if (isset($data) && $data !== null) {
                foreach ($data as $row) {
                    echo "<tr>
                            <td>" . $row['nombre'] . "</td>
                            <td>" . $row['Nombre'] . "</td>
                            <td>" . number_format($row['total_horas_extras'], 2) . "</td>
                            <td>" . number_format($row['monto_pago'], 2) . "</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='no-records'>No se encontraron registros de horas extras.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    </div>
    <script>
        document.getElementById('btnFiltrar').addEventListener('click', function (e) {
            const usuario = document.getElementById('usuario').value;
            const departamento = document.getElementById('departamento').value;

            if (!usuario && !departamento) {
                e.preventDefault(); // Detiene el envío del formulario
                alert('Debe seleccionar un usuario o un departamento para filtrar.');
            }
        });
    </script>
</body>

</html>


<style>
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;

    }

    .resultado {
        text-align: center;
        /* Asegura que el texto se centre */
        font-size: 18px;
        color: #333;
        margin-top: 20px;
        background-color: rgb(160, 255, 180);
        /* Fondo verde */
        padding: 15px;
        /* Espaciado interno */
        border-radius: 8px;
        /* Bordes redondeados */
        max-width: 600px;
        /* Limita el ancho máximo */
        margin-left: auto;
        /* Centra el bloque */
        margin-right: auto;
        /* Centra el bloque */

    }



    .container {
        width: 40%;
        /* Ajusta el ancho para hacer la card más pequeña */
        max-width: 800px;
        /* Limita el ancho máximo */
        margin-top: 100px;
        padding: 20px 40px;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
        margin-left: auto;
        margin-right: auto;
    }



    h1 {
        text-align: center;
        color: #333;
        margin-bottom: 50px;
        margin-right: 10%;
        font-weight: bold;
        font-size: 24px;

    }

    h3 {
        text-align: center;
        color: black;
        margin-bottom: 50px;
        margin-right: 10%;
        font-weight: bold;
    }

    .btn {
        background-color: #0B4F6C;
        color: white;
        padding: 10px 20px;
        font-size: 16px;
        border-radius: 5px;
        margin-right: 10px;
        /* Espacio entre los botones */
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-secondary {
        background-color: #555;
        /* Gris para el botón "Devolver" */
        color: white;
    }

    .btn:hover {
        background-color: #0E5D6A;
    }

    .btn-secondary:hover {
        background-color: #444;
        /* Gris más oscuro para el hover */
    }


    .btn:active {
        background-color: #0E5D6A;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);

    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);

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
        background-color: #116B67;
        color: #fff;
        text-align: center;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    td {
        background-color: #f9f9f9;
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
        background-color: #0E5D6A;
    }

    .modal-content a:hover {
        background-color: darkgray;
    }



    .button-group {
        display: flex;
        justify-content: space-between;
        /* Alinea a la izquierda, usa center o space-between si prefieres */
        align-items: center;
        /* Alineación vertical */
        gap: 15px;
        /* Espaciado entre los botones */
        margin-bottom: 20px;
    }

    .button-group form {
        display: inline-block;
    }


    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        /* Espacio entre los elementos */
        align-items: center;
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
        max-width: 600px;
        margin: auto;
        justify-content: center;

    }

    .form-group {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-width: 200px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.8);
    }

    label {
        font-size: 16px;
        font-weight: bold;
        color: #333;
        margin-bottom: 5px;
    }

    select {
        width: 100%;
        padding: 10px;
        font-size: 16px;
        border: 2px solidrgb(15, 15, 15);
        border-radius: 5px;
        background: #f9f9f9;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
    }

    select:hover {
        border-color: #a88c4a;
    }

    select:focus {
        outline: none;
        border-color: #805d24;
        box-shadow: 0 0 5px rgba(200, 150, 60, 0.6);
    }

    /* Botón estilizado */
    .btn {
        background-color: #0E5D6A;
        color: white;
        padding: 10px 15px;
        font-size: 16px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn:hover {
        background-color: #0E5D6A;
    }


    .form-container {
        display: flex;
        justify-content: start;
        /* Centra el contenido horizontalmente */
        align-items: center;
        /* Centra el contenido verticalmente */
        width: 100%;
        /* Asegura que el contenedor ocupe todo el ancho disponible */
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        /* Espacio entre los elementos */
        align-items: center;
        background: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
        max-width: 600px;
        /* Tamaño máximo para la card */
        margin: auto;
        justify-content: center;
        width: 80%;
        /* Reducción de ancho para que la card sea más pequeña */
    }
</style>

</html>