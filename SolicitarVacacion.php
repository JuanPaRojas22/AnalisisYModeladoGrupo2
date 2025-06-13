<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php';
require_once __DIR__ . '/Impl/VacacionDAOSImpl.php';
require_once __DIR__ . '/Impl/historialVacacionesDAOSImpl.php';
require_once __DIR__ . '/Impl/Historial_Solicitud_Modificacion_VacacionesDAOSImpl.php';
include "template.php";

// Obtener el ID del departamento del usuario desde la sesión
$id_departamento = $_GET['id_departamento'] ?? null;

// Se inicializan las clases UsuarioDAO, VacacionDAO y HistorialVacacionDAO 
$UsuarioDAO = new UsuarioDAOSImpl();
$VacacionDAO = new VacacionDAOSImpl();
$HistorialVacacionDAO = new historialVacacionesDAOSImpl();
$Historial_Solicitud_Modificacion_VacacionesDAO = new Historial_Solicitud_Modificacion_VacacionesDAOSImpl();
$id_usuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;

// Obtiene los detalles del departamento de usuario por id
$userDepartmentData = $UsuarioDAO->getUserDepartmentById($id_usuario);
$userDepartment = $userDepartmentData ? $userDepartmentData['id_departamento'] : null;

// Obtiene los dias restantes de vacaciones del usuario para mostrarselos en la vista
$diasRestantes = $HistorialVacacionDAO->getDiasRestantes($id_usuario);

// Obtener los dias reservados por el empleado para que no pueda solicitar vacaciones en esas fechas
$fechasReservadas = $VacacionDAO->getFechasReservadasEmpleado($id_usuario);

$rangosFechas = array_map(function ($row) {
    return ["from" => $row['fecha_inicio'], "to" => $row['fecha_fin']];
}, $fechasReservadas);

// Mostrar las fechas reservadas en formato JSON para el calendario
//echo json_encode($rangosFechas);

// Logica para crear una vacacion utilizando el metodo de IngresarVacacion 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturar datos del formulario
    $fecha_inicio = $_POST['fecha_inicio'] ?? '';
    $fecha_fin = $_POST['fecha_fin'] ?? '';
    $diasTomado = $_POST['diasTomado'] ?? '';
    $razon = $_POST['razon'] ?? '';
    $observaciones = $_POST['observaciones'] ?? '';
    $id_usuario = $id_usuario;
    // Tengo que ingresar el historial de vacaciones del usuario actual
    $id_historial = $HistorialVacacionDAO->getHistorialVacaciones($id_usuario);
    $fechacreacion = date("Y-m-d H:i:s");
    $usuariocreacion = "admin";
    $fechamodificacion = date("Y-m-d H:i:s");
    $usuariomodificacion = "admin";
    $id_estado_vacacion = 1;
    $SolicitudEditar = 'No';

    // Validar campos obligatorios
    $errores = [];
    if (empty($fecha_inicio))
        $errores[] = "La fecha de inicio es obligatoria.";
    if (empty($fecha_fin))
        $errores[] = "La fecha de fin es obligatoria.";
    if (empty($diasTomado))
        $errores[] = "Los días tomados son obligatorios.";
    if (empty($razon))
        $errores[] = "La razón es obligatoria.";
    if (empty($observaciones))
        $errores[] = "Las observaciones son obligatorias.";
    if (empty($id_usuario))
        $errores[] = "El id del usuario es obligatorio.";
    //if (empty($id_historial)) $errores[] = "El id del historial es obligatorio.";
    if (empty($fechacreacion))
        $errores[] = "La fecha de creación es obligatoria.";
    if (empty($usuariocreacion))
        $errores[] = "El usuario de creación es obligatorio.";
    if (empty($fechamodificacion))
        $errores[] = "La fecha de modificación es obligatoria.";
    if (empty($usuariomodificacion))
        $errores[] = "El usuario de modificación es obligatorio.";
    if (empty($id_estado_vacacion))
        $errores[] = "El id del estado de vacación es obligatorio.";
    if (empty($SolicitudEditar))
        $errores[] = "La solicitud de edición es obligatoria.";
    if (empty($fecha_fin))
        $errores[] = "La fecha de fin";

    if (empty($errores)) {
        $resultado = $VacacionDAO->IngresarVacacion(
            $razon,
            $diasTomado,
            $fecha_inicio,
            $observaciones,
            $id_usuario,
            $id_historial,
            $fechacreacion,
            $usuariocreacion,
            $fechamodificacion,
            $usuariomodificacion,
            $id_estado_vacacion,
            $SolicitudEditar,
            $fecha_fin
        );

        if (!empty($resultado)) {
            // Si el metodo devuelve errores, se guardan en el array de errores
            $errores = array_merge($errores, $resultado);
        } else {
            $mensaje_exito = "Solicitud de vacaciones ingresada correctamente.";
        }
    }

    // Generar PDF
    // Generar reporte de vacaciones
    $sql = "SELECT 
                    v.id_vacacion,
                    u.nombre AS empleado,
                    d.nombre AS departamento,
                    v.razon,
                    v.diasTomado,
                    v.fecha_inicio,
                    v.fecha_fin,
                    h.DiasRestantes,
                    ev.descripcion AS estado
            FROM vacacion v
                    LEFT JOIN usuario u ON v.id_usuario = u.id_usuario
                    LEFT JOIN departamento d ON u.id_departamento = d.id_departamento
                    LEFT JOIN estado_vacacion ev ON v.id_estado_vacacion = ev.id_estado_vacacion
                    INNER JOIN historial_vacaciones h ON v.id_historial = h.id_historial
            WHERE 
                    h.id_usuario = ? AND
                    (v.fecha_inicio BETWEEN ? AND ? 
                    OR v.fecha_fin BETWEEN ? AND ? 
                    OR (v.fecha_inicio <= ? AND v.fecha_fin >= ?))";

    $params = [$id_usuario, $fecha_inicio, $fecha_fin, $fecha_inicio, $fecha_fin, $fecha_inicio, $fecha_fin];
    //$params = [];
    //if ($id_usuario) { 
    //$sql .= " AND h.id_usuario = ?";
    //$params[] = $id_usuario;
    //}
    if ($userDepartment) {
        $sql .= " AND u.id_departamento = ?";
        $params[] = $userDepartment;
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param(str_repeat("s", count($params)), ...$params); // Ensure the correct type and count
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $historial = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>Gestión de Usuarios</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />

    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style2.css">


    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
        .profile-container {
            margin-left: 250px;
            padding: 60px;
        }

        td,
        div {
            color: black !important;
        }
        
    </style>
</head>

<body>

    <section id="container">


        <section id="main-content">
            <section class="wrapper site-min-height">

                <!-- /MAIN CONTENT -->
                 <?php
                // Verificar si el usuario está logueado
                // Conexión a la base de datos
// Parámetros de conexión
/*$host = "accespersoneldb.mysql.database.azure.com";
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
mysqli_set_charset($conn, "utf8mb4");*/

                // Consulta para obtener el departamento del usuario
                
                //$search = isset($_GET['search']) ? (int) $_GET['search'] : null;
                $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                $limit = 5;
                $offset = ($page - 1) * $limit;

                $filtro = $_GET['filtro'] ?? 'pendiente';
                switch($filtro) {
                case 'pendiente':
                    $result = $VacacionDAO->getVacacionesPorEstado($id_usuario, [1], $limit, $offset);
                    break;
                case 'aprobadas':
                    $result = $VacacionDAO->getVacacionesPorEstado($id_usuario, [2,4], $limit, $offset);
                    break;
                case 'rechazadas':
                    $result = $VacacionDAO->getVacacionesPorEstado($id_usuario, [3], $limit, $offset);
                    break;
                case 'modificadas':
                    // obtiene exclusivamente las vacaciones modificadas aprobadas o pendientes
                    $result = $Historial_Solicitud_Modificacion_VacacionesDAO->getHistorialSolicitudModificacionPorUsuario($id_usuario, $limit, $offset);
                    break;
                default:
                    $result = $VacacionDAO->getVacacionesPorEstado($id_usuario, [1], $limit, $offset);
                }


                ?>-->

                <html lang="es">

                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Listado Vacaciones</title>
                    <!-- Flatpickr CSS -->
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
                    <style>
                        body {
                            font-family: 'Ruda', sans-serif;
                            background-color: #f7f7f7;
                            margin: 0;
                            padding: 0;
                        }

                        .container {
                            max-width: 80%;
                            /* Reducir el ancho del contenedor */
                            margin: 50px auto;
                            /* Centrar y dar un margen desde arriba */
                            padding: 20px;
                            background-color: #ffffff;
                            border-radius: 10px;
                            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                        }

                        h1 {
                            text-align: center;
                            color: #333;
                            margin-bottom: 50px;
                            margin-right: 10%;
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
                            background-color: #147964;
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
                            background-color: #147964;
                        }

                        .btn:active {
                            background-color: #147964;
                        }

                        table {
                            width: 80%;
                            /* Reducir el ancho de la tabla */
                            margin: 20px auto;
                            /* Centrar la tabla */
                            border-collapse: collapse;
                            border-radius: 8px;
                            overflow: hidden;
                        }

                        table .btn {
                            font-size: 12px;
                            /* Reducir el tamaño del texto */
                            padding: 6px 10px;
                            /* Reducir el padding para hacer el botón más pequeño */
                            border-radius: 4px;
                            /* Opcional: ajustar los bordes si lo deseas */
                            margin: 2px;
                            /* Añadir un pequeño margen entre botones */
                            display: inline-block;
                        }

                        /* Para asegurarte de que los botones se ven bien */
                        table .btn i {
                            font-size: 14px;
                            /* Reducir el tamaño del icono dentro de los botones */
                        }

                        /* Alineación y tamaño de las celdas */
                        th,
                        td {
                            padding: 10px;
                            /* Reducir el padding */
                            text-align: left;
                            font-size: 14px;
                            /* Reducir el tamaño de la fuente */
                            color: #555;
                            border-bottom: 1px solid #ddd;
                        }

                        th {
                            background-color: #116B67;
                            color: #fff;
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

                        .close-button {
                            border: none;
                            display: inline-block;
                            padding: 8px 16px;
                            vertical-align: middle;
                            overflow: hidden;
                            text-decoration: none;
                            color: inherit;
                            background-color: inherit;
                            text-align: center;
                            cursor: pointer;
                            white-space: nowrap
                        }

                        .topright {
                            position: absolute;
                            right: 0;
                            top: 0
                        }

                        .flatpickr-day.reservado {
                            background-color: red !important;
                            color: white !important;
                            border-radius: 50%;
                        }

                        .row-fechas-pdf {
                            display: flex;
                            justify-content: space-between;
                            /* Coloca los elementos con espacio entre ellos */
                            gap: 20px;
                            /* Espacio entre los campos */
                            align-items: center;
                            /* Centra los elementos verticalmente */
                            margin-bottom: 20px;
                        }

                        /* Ajuste de los campos de fecha para estar alineados */
                        input[type="date"] {
                            width: 48%;
                            /* Hacer que los campos de fecha sean más pequeños */
                            padding: 8px;
                            font-size: 14px;
                            border-radius: 5px;
                            border: 1px solid #ddd;
                        }


                        button[type="submit"],
                        button {
                            background-color: #0B4F6C;
                            color: white;
                            padding: 10px 20px;
                            /* Tamaño adecuado para los botones */
                            font-size: 16px;
                            border: none;
                            border-radius: 5px;
                            cursor: pointer;
                            width: auto;
                            /* Ajustar al tamaño necesario */
                            display: inline-block;
                            /*margin-top: 10px;*/
                            transition: background-color 0.3s;
                        }

                        button[type="submit"]:hover,
                        button:hover {
                            background-color: #0A3D55;
                            /* Color más oscuro al pasar el ratón */
                        }

                        .input-group .btn {
                            margin-top: 10 !important;
                        }

                        .search-bar {
                            transition: width 0.3s ease-in-out;
                            width: 40px;
                        }

                        .search-bar:focus {
                            width: 250px;
                        }

                        .expanding-search {
                            transition: all 0.4s ease-in-out;
                            width: 50px;
                            background-color: #f1f1f1;
                            border: 1px solid #ccc;
                        }

                        /* 🔍 Expanding search solo con CSS */
                        .expanding-search {
                            width: 5px;
                            transition: width 0.4s ease-in-out;
                            padding-left: 15px;
                        }

                        .expanding-search:focus {
                            width: 250px;
                        }

                        .pagination {
                            width: 80%;
                            margin: 20px auto 0 auto;
                            /* alineado con la tabla (80%) */
                            justify-content: flex-end;
                            padding-right: 20px;
                        }

                        .pagination .page-link {
                            color: #147964;
                            background-color: #f9f9f9;
                            border: 1px solid #ddd;
                            font-weight: bold;
                        }

                        .pagination .page-item.active .page-link {
                            background-color: #116B67;
                            color: white;
                            border-color: #116B67;
                        }
                        /* Estilo base para los tabs */
            .nav-tabs .nav-link {
    color: #147964;
    background-color: #f9f9f9;
    border: 1px solid #ddd;
    font-weight: bold;
}

/* Estilo cuando están activos (seleccionados) */
.nav-tabs .nav-link.active {
    background-color: #116B67;
    color: white;
    border-color: #116B67 #116B67 #f9f9f9;
}

/* Opcional: quitar borde inferior del tab activo */
.nav-tabs {
    border-bottom: 1px solid #ddd;
}

                    </style>
                </head>

                <body>
                    <div class="container">
                        <h1>Mis Vacaciones</h1>

                        <!-- Botones para Solicitar Vacación y Medio Día -->
                        <div class="row"
                            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <button onclick="document.getElementById('id01').style.display='block'">
                                Solicitar Vacacion
                            </button>

                            <button onclick="window.location.href='SolicitarMedioDia.php'">
                                Solicitar Medio Día
                            </button>

                            <div
                                style="background-color: #d4edda; color: #155724; padding: 10px 20px; border-radius: 5px; text-align: center; font-size: 16px;">
                                <strong>Días Restantes:</strong> <?php echo $diasRestantes; ?>
                            </div>
                        </div>


                        <!-- Fechas inicio y fin con botón PDF y buscador -->
                        <div class="row-fechas-pdf d-flex align-items-center gap-2 flex-wrap">
                            <label for="fecha_inicio">Fecha Inicio:</label>
                            <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" required
                                style="flex: 1; width: 200px;">

                            <label for="fecha_fin">Fecha Fin:</label>
                            <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" required
                                style="flex: 1; width: 200px;">

                            <form action="generar_reporteVacaciones.php" method="GET"
                                style="display: inline-block; width: auto;">
                                <input type="hidden" name="id_usuario" value="<?= htmlspecialchars($id_usuario) ?>">
                                <input type="hidden" name="id_departamento"
                                    value="<?= htmlspecialchars($id_departamento) ?>">
                                <button type="submit" class="btn btn-success">Descargar PDF</button>

                            </form>

                        </div>


                        <!-- Buscador colocado a la derecha -->
                        <div class="mb-3" style="margin-left: 30%;">
                            <input type="date" id="buscarFecha" class="form-control" style="width: 400px;" />
                        </div>



                    </div>
                    <div id="id01" class="modal">
                        <!-- Mostrar errores -->
                        <?php if (!empty($errores)): ?>
                            <div
                                style="color: red; background: #ffcccc; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                <strong>Errores:</strong>
                                <ul>
                                    <?php foreach ($errores as $error): ?>
                                        <li><?= htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <!-- Mostrar mensaje de éxito -->
                        <?php if (!empty($mensaje_exito)): ?>
                            <div
                                style="color: green; background: #ccffcc; padding: 10px; border-radius: 5px; margin-bottom: 10px;">
                                <?= htmlspecialchars($mensaje_exito); ?>
                            </div>
                        <?php endif; ?>

                        <span onclick="document.getElementById('id01').style.display='none'" class="close"
                            title="Close Modal">&times;</span>
                        <form class="modal-content" action="SolicitarVacacion.php" method="POST"
                            enctype="multipart/form-data">
                            <div class="container">
                                <header style="background-color:#000;color:#fff;">
                                    <span onclick="document.getElementById('id01').style.display='none'"
                                        class="close-button topright">&times;</span>
                                </header>
                                <h1>Registrar Vacación</h1>
                                <p>Ingrese los datos correspondientes</p>
                                <br>
                                <label for="razon">Razón:</label>
                                <input type="text" id="razon" name="razon" class="form-control"
                                    placeholder="Ingrese su razón" autofocus>

                                <label for="diasTomado">Días Tomados:</label>
                                <input type="number" id="diasTomado" name="diasTomado" class="form-control"
                                    placeholder="Ingrese los días tomados" autofocus>

                                <label for="fecha_inicio_solicitud">Fecha Inicio:</label>
                                <input type="text" id="fecha_inicio_solicitud" name="fecha_inicio" class="form-control"
                                    placeholder="Ingrese la fecha de inicio" autofocus>

                                <label for="fecha_fin_solicitud">Fecha Fin:</label>
                                <input type="text" id="fecha_fin_solicitud" name="fecha_fin" class="form-control"
                                    placeholder="Ingrese la fecha de fin" autofocus>

                                <label for="observaciones">Observaciones:</label>
                                <input type="text" id="observaciones" name="observaciones" class="form-control"
                                    placeholder="Ingrese sus observaciones" autofocus>

                                <div class="clearfix">
                                    <button type="submit" class="signupbtn">Ingresar</button>
                                </div>
                            </div>
                        </form>
                        <!-- Flatpickr JS -->
                        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
                        <script>
                            // Fechas reservadas (ejemplo)
                            const fechasReservadas = <?php echo json_encode($rangosFechas); ?>;

                            function configurarCalendario(idCampo) {
                                flatpickr(idCampo, {
                                    dateFormat: "Y-m-d",
                                    disable: fechasReservadas.map(date => ({ from: date, to: date })), // Se deshabilitan fechas reservadas
                                    onDayCreate: function (dObj, dStr, fp, dayElem) {
                                        const date = dayElem.dateObj.toISOString().split('T')[0];
                                        // Verificar si la fecha está reservada
                                        fechasReservadas.forEach(range => {
                                            if (date >= range.from && date <= range.to) {
                                                dayElem.classList.add("reservado");
                                                dayElem.style.pointerEvents = "none";  // Bloquea la selección
                                                dayElem.style.opacity = "0.5";  // Hace que parezcan deshabilitadas
                                            }
                                        });
                                    }
                                });
                            }

                            configurarCalendario("#fecha_inicio_solicitud");
                            configurarCalendario("#fecha_fin_solicitud");
                        </script>
                    </div>
                    
                        <ul class="nav nav-tabs justify-content-center mb-3 ">
                            <li class="nav-item">
                                <a class="nav-link <?= $filtro === 'pendiente' ? 'active' : '' ?>" href="?filtro=pendiente">Pendientes</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $filtro === 'aprobadas' ? 'active' : '' ?>" href="?filtro=aprobadas">Aprobadas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $filtro === 'rechazadas' ? 'active' : '' ?>" href="?filtro=rechazadas">Rechazadas</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $filtro === 'modificadas' ? 'active' : '' ?>" href="?filtro=modificadas">Modificadas</a>
                            </li>
                        </ul>

                    <!-- <a href="EditarVacaciones.php">Editar Vacaciones</a> -->
                    <link rel="stylesheet"
                        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Apellido</th>
                                    <th>Departamento</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Dias Tomados</th>
                                    <th>Dias Restantes</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Mostrar los resultados de la consulta
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row['nombre'] . "</td>";
                                        echo "<td>" . $row['apellido'] . "</td>";
                                        echo "<td>" . $row['Departamento'] . "</td>";

                                        if ($filtro === 'modificadas') {
                                            echo "<td>{$row['fecha_inicio']}</td>";
                                            echo "<td>{$row['fecha_fin']}</td>";
                                            echo "<td>{$row['dias_solicitados']}</td>";
                                            echo "<td>{$row['DiasRestantes']}</td>";
                                            echo "<td>{$row['estado_modificacion']}</td>";
                                        } else {
                                            echo "<td>{$row['fecha_inicio']}</td>";
                                            echo "<td>{$row['fecha_fin']}</td>";
                                            echo "<td>{$row['diasTomado']}</td>";
                                            echo "<td>{$row['DiasRestantes']}</td>";
                                            echo "<td>{$row['descripcion']}</td>";
                                        }

                                        if ($filtro !== 'modificadas') {
                                            echo "<td>
                                                <div class='d-flex flex-column gap-2'>  
                                                    <a class='btn btn-primary' style='font-size: 2.5rem;' href='detalleVacacionSolicitada.php?id=" . $row['id_vacacion'] . "' >
                                                        <i class='bi bi-file-earmark-person'></i> 
                                                    </a>";
                                        } else{
                                            echo "<td>
                                    <a class='btn btn-success' style='font-size: 2.5rem;' href='detalleEditarVacacion.php?id=" . $row['id_registro'] . "' >
                                        <i class='bi bi-file-earmark-person'></i> 
                                    </a>
                                </td>";
                                        }

                                        

                                        if ($filtro !== 'modificadas') {
                                            $puedeEditar = $VacacionDAO->puedeEditarVacacion($row['id_vacacion']);
                                            if ($puedeEditar) {
                                                echo "<a class='btn btn-success' style='font-size: 2.5rem;' href='SolicitarEdicionVacacion.php?id=" . $row['id_vacacion'] . "' >
                                                        <i class='bi bi-pencil-square'></i> 
                                                    </a>";
                                            } else {
                                                echo "<button class='btn btn-secondary' style='font-size: 2.5rem;' onclick='alert(\"No se puede editar esta vacación. Deben faltar al menos 8 días para su inicio.\")'>
                                                        <i class='bi bi-pencil-square'></i> 
                                                    </button>";
                                            }
                                        }

                                        echo "</div></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='9' class='no-records'>No se encontraron registros.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>


                        <?php
                            // Obtener total de resultados según el filtro actual
                            $totalFilas = 0;
                            switch ($filtro) {
                                case 'pendiente':
                                    $totalFilas = $VacacionDAO->contarVacacionesPorEstado($id_usuario, [1]);
                                    break;
                                case 'aprobadas':
                                    $totalFilas = $VacacionDAO->contarVacacionesPorEstado($id_usuario, [2,4]);
                                    break;
                                case 'rechazadas':
                                    $totalFilas = $VacacionDAO->contarVacacionesPorEstado($id_usuario, [3]);
                                    break;
                                case 'modificadas':
                                    $totalFilas = $Historial_Solicitud_Modificacion_VacacionesDAO->contarHistorialModificadoPorUsuario($id_usuario);
                                    break;
                                default:
                                    $totalFilas = $VacacionDAO->contarVacacionesPorEstado($id_usuario, [1]);
                                    break;
                            }

                            $totalPaginas = ceil($totalFilas / $limit);
                            ?>

                            <nav aria-label="Paginación">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&filtro=<?= $filtro ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>

            </section>
            <script>
                // Función para abrir el modal
                function abrirModal(modalId) {
                    document.getElementById(modalId).style.display = 'flex';
                }

                // Función para cerrar el modal
                function cerrarModal(modalId) {
                    document.getElementById(modalId).style.display = 'none';
                }

                // Get the modal
                var modal = document.getElementById('id01');

                // When the user clicks anywhere outside of the modal, close it
                window.onclick = function (event) {
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }

                    document.getElementById('buscarFecha').addEventListener('input', function () {
                        const fechaBuscada = this.value;
                        const filas = document.querySelectorAll('table tbody tr');

                        filas.forEach(fila => {
                            const fechaInicio = fila.children[3].textContent.trim();
                            if (fechaInicio.includes(fechaBuscada) || fechaBuscada === "") {
                                fila.style.display = "";
                            } else {
                                fila.style.display = "none";
                            }
                        });
                    });
                


            </script>
</body>

</html>
