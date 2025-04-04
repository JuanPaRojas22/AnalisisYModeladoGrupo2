<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php';
require_once __DIR__ . '/Impl/Historial_Solicitud_Modificacion_VacacionesDAOSImpl.php';
include "template.php";

// Instancia el DAO
$UsuarioDAO = new UsuarioDAOSImpl();
$Historial_Solicitud_Modificacion_VacacionesDAO = new Historial_Solicitud_Modificacion_VacacionesDAOSImpl();

// Verifica si el parámetro 'id' está presente en la URL
if (isset($_GET['id'])) {
    $id_historial_solicitud_modificacion = $_GET['id'];

    // Obtiene el id del usuario de la vacacion actual
    $id_usuario = $Historial_Solicitud_Modificacion_VacacionesDAO->getUserByIdHistorialSolicitudModificacion($id_historial_solicitud_modificacion);
    
    // Obtiene los detalles del usuario por id
    $user = $UsuarioDAO->getUserById($id_usuario);

    // Obtiene el historial de solicitudes de vacacionesa a modificar de los usuarios del departamento del administrador actual
    $Historial_Solicitud_Modificacion_Vacaciones = $Historial_Solicitud_Modificacion_VacacionesDAO->getHistorialSolicitudModificacionVacaciones($id_historial_solicitud_modificacion);    

    // Si el usuario no existe
    if (!$user) {
        echo "Usuario no encontrado.";
        exit;
    }
} else {
    echo "ID de usuario no proporcionado.";
    exit;
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
        body {
            font-family: 'Ruda', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 2000px;
            margin: 50px auto 200px 250px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 50px;
            margin-right: 10%;
            font-weight: bold;
        }

        .user-img {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .user-img img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #c9aa5f;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
        }

        th, td {
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

        td {
            background-color: #f9f9f9;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
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
            transition: background-color 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);
        }

        .btn:hover {
            background-color: darkgray;
        }
    </style>
</head>

<body>

    <!--main content start-->
    <div class="container">
        <h1>Solicitud de Modificación de Vacaciones</h1>
        <div class="user-img">
            <?php if (!empty($user['direccion_imagen'])): ?>
                <img src="<?php echo htmlspecialchars($user['direccion_imagen']); ?>" alt="Imagen del usuario">
            <?php else: ?>
                <p>No hay imagen disponible</p>
            <?php endif; ?>
        </div>

        <table class="details-table">
            <tr><th>Nombre</th><td><?php echo htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['Nombre']); ?></td></tr>
            <tr><th>Apellido</th><td><?php echo htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['Apellido']); ?></td></tr>
            <tr><th>Departamento</th><td><?php echo htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['Departamento']); ?></td></tr>
            <tr><th>Nueva Fecha de Inicio</th><td><?php echo htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['NuevaFechaInicio']); ?></td></tr>
            <tr><th>Nueva Fecha de Fin</th><td><?php echo htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['NuevaFechaFin']); ?></td></tr>
            <tr><th>Nuevos Días Solicitados</th><td><?php echo htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['NuevosDiasSolicitados']); ?></td></tr>
            <tr><th>Fecha Inicio Original</th><td><?php echo htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['OriginalFechaInicio']); ?></td></tr>
            <tr><th>Fecha Fin Original</th><td><?php echo htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['OriginalFechaFin']); ?></td></tr>
            <tr><th>Días Solicitados Originalmente</th><td><?php echo htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['OriginalDiasSolicitados']); ?></td></tr>
            <tr><th>Días Restantes</th><td><?php echo htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['DiasRestantes']); ?></td></tr>
            <tr><th>Estado Solicitud</th><td><?php echo htmlspecialchars($Historial_Solicitud_Modificacion_Vacaciones['EstadoSolicitudVacacion']); ?></td></tr>
        </table>

        <div class="btn-container">
            <a href="EditarVacaciones.php" class="btn btn-secondary">Volver</a>
            <div>
                <a href="procesarEditarVacacion.php?id=<?php echo $id_historial_solicitud_modificacion; ?>&accion=aprobar" class="btn btn-success">Aprobar</a>
                <a href="procesarEditarVacacion.php?id=<?php echo $id_historial_solicitud_modificacion; ?>&accion=rechazar" class="btn btn-danger">Denegar</a>
            </div>
        </div>
    </div>

        <!-- Estilos CSS -->
        <style>
            h1 {
                text-align: center;
                font-size: 24;
                color: black;
            }

            /* Estilo para la tabla de detalles del usuario */
            .user-details {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
                font-size: 15px;


            }

            .user-details th,
            .user-details td {
                padding: 20px;
                text-align: left;
                border: 8px solid #ddd;
                border-color: rgb(119, 152, 189);
                color: rgb(20, 20, 20);

            }

            .user-details th {
                background-color: #f4f4f4;
                font-weight: bold;
            }

            .user-details td {
                background-color: rgb(255, 255, 255);
            }

            .user-details tr:nth-child(even) td {
                background-color: #f1f1f1;
            }

            .btn {

                padding: 10px 20px;
                /* Ajusta el tamaño del botón */
                margin-top: 10px;
                /* Agregar margen superior */
                cursor: pointer;
                border-radius: 5px;
                text-decoration: none;
                border: 1px solid transparent;
                display: inline-block;
                text-align: center;
                /* Centra el texto dentro del botón */
                width: auto;
            }
        </style>

</body>

</html>