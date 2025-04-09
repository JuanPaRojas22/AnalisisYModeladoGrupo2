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

</head>

<body>


        

<section id="main-content">
        <section class="wrapper site-min-height">
            <!-- Botón para generar el PDF -->


            <div class="container">
                <h1>Solicitud de Modificación de Vacaciones</h1>
                <div class="btn-container-wrapper">
                    <form method="get" action="EditarVacaciones.php" accept-charset="UTF-8">
                        <input type="hidden" name="id_usuario" value="<?php echo $user['id_usuario']; ?>">
                        <button type="submit" class="btn-container"><i class="bi bi-arrow-return-left"></i></button>
                    </form>
                <div>
                            
                <a href="procesarEditarVacacion.php?id=<?php echo $id_historial_solicitud_modificacion; ?>&accion=aprobar" class="btn-aprove"><i class="bi bi-check-circle-fill"></i></a>
                <a href="procesarEditarVacacion.php?id=<?php echo $id_historial_solicitud_modificacion; ?>&accion=rechazar" class="btn-decline"><i class="bi bi-x-square-fill"></i></a>
                    
                </div>


                </div>
                <div class="user-img">
                    <?php if (!empty($user['direccion_imagen'])): ?>
                        <img src="<?php echo htmlspecialchars($user['direccion_imagen']); ?>" alt="Imagen del usuario">
                    <?php else: ?>
                        <p>No hay imagen disponible</p>
                    <?php endif; ?>
                </div>

                <table class="user-details">
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
            </div>

        </section>
    </section>


    <style>
        body {
            font-family: 'Ruda', sans-serif;
            background-color: #f7f7f7;  /* Blanco cremoso */
            /* Gris suave */
            margin: 0;
            padding: 0;
        }

        .container {
            width: 50%;
            max-width: 40%;
            /* Limitar el ancho máximo */
            margin: 5px auto;
            padding: 20px;
            background-color: #f7f7f7;  /* Blanco cremoso */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-weight: bold;
        }

        .user-img {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .user-img img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #c9aa5f;
        }

        table {
            width: 50%;
            border-collapse: separate;
            /* Cambiar a 'separate' para que los bordes se muestren correctamente */
            border-spacing: 0;
            /* Eliminar el espacio entre celdas */
            margin-top: 20px;
            margin-left: 25%;
            border-radius: 10px;
            /* Borde redondeado en la tabla */
            overflow: hidden;
            /* Para que los bordes redondeados se vean en las celdas */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
            /* Agregar sombra ligera */
        }

        th,
        td {
            padding: 8px 8px;
            /* Reducir el espacio dentro de las celdas */
            text-align: center;
            font-size: 12px;
            /* Reducir el tamaño de la fuente */
            color: #fff;
            border-bottom: 1px solid #ddd;

        }

        th {
            background-color: #bea66a;
            color: #fff;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        td {
            background-color: #bea66a;
        }

        .btn-container-wrapper {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn-container {
            background-color: #c9aa5f;
            color: white;
            padding: 8px 12px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .btn-container:hover {}
        .btn-aprove {
            display: inline-block;
            background-color: #c9aa5f;
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
            background-color: rgb(0, 255, 34);
        }

        .btn-decline {
            display: inline-block;
            background-color: #c9aa5f;
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
            background-color: rgb(255, 0, 0);
        }
    </style>

</body>

</html>