<?php
session_start();
require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php';
require_once __DIR__ . '/Impl/VacacionDAOSImpl.php';
include "template.php";

// $UsuarioDAO = new UsuarioDAOSImpl();
// $user_id = $_SESSION['id_usuario'];

// Instancia el DAO
$UsuarioDAO = new UsuarioDAOSImpl();
$VacacionDAO = new VacacionDAOSImpl(); 
// Verifica si el parámetro 'id' está presente en la URL
if (isset($_GET['id'])) {
    $id_vacacion = $_GET['id'];
    // Obtiene el id del usuario de la vacacion actual
    $id_usuario = $VacacionDAO->getUserByIdVacacion($id_vacacion);

    // Obtiene los detalles del usuario por id
    $user = $UsuarioDAO->getUserById($id_usuario);

    // Obtiene la vacacion actual del usuario
    //$vacacion = $UsuarioDAO->getVacacionByUserId($id_usuario);

    // Obtiene las vacaciones del usuario actual
    $vacaciones = $VacacionDAO->getDetalleVacacion($id_vacacion);

    // Obtiene los historiales de vacaciones del usuario actual
    $historial_vacaciones = $UsuarioDAO->getHistorialVacacionesByUserId($id_usuario);
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
    </style>
</head>

<body>

    <!--main content start-->
    <section id="main-content">
            <section class="wrapper site-min-height">
                <!-- Botón para generar el PDF -->

                <h1 text-center>Solicitud de vacacion</h1>
                <table class="user-details">
                <!-- Enlace para volver a la lista de usuarios -->
                <a href="SolicitarVacacion.php" class="btn btn-success">Volver</a>
                    <tr>
                        <td>
                            <?php
                            if (!empty($user['direccion_imagen'])): ?>
                                <img src="<?php echo htmlspecialchars($user['direccion_imagen']); ?>"
                                    alt="Imagen del usuario" width="100" height="100">
                            <?php else: ?>
                                <p>No hay imagen disponible</p>

                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <th>Nombre</th>
                        <td><?php echo htmlspecialchars($user['nombre']); ?></td>
                    </tr>
                    <tr>
                        <th>Apellido</th>
                        <td><?php echo htmlspecialchars($user['apellido']); ?></td>
                    </tr>
                    <?php foreach ($vacaciones as $vacacion): ?>
                    <tr>
                        <th>Fecha de inicio</th>
                        <td><?php echo htmlspecialchars($vacacion['fecha_inicio']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php foreach ($vacaciones as $vacacion): ?>
                    <tr>
                        <th>Fecha Fin</th>
                        <td><?php echo htmlspecialchars($vacacion['fecha_fin']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php foreach ($vacaciones as $vacacion): ?>
                    <tr>
                        <th>Dias tomados</th>
                        <td><?php echo htmlspecialchars($vacacion['diasTomado']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php foreach ($historial_vacaciones as $historial_vacacion): ?>
                    <tr>
                        <th>Dias restantes</th>
                        <td><?php echo htmlspecialchars($historial_vacacion['DiasRestantes']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php foreach ($vacaciones as $vacacion): ?>
                    <tr>
                        <th>Razon</th>
                        <td><?php echo htmlspecialchars($vacacion['razon']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php foreach ($vacaciones as $vacacion): ?>
                    <tr>
                        <th>Estado</th>
                        <td>
                            <?php
                            // Obtener la descripción del estado de vacación
                            $estado_vacacion = $UsuarioDAO->getEstadoVacacionById($vacacion['id_estado_vacacion']);
                            echo htmlspecialchars($estado_vacacion['descripcion']);
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>

                    
                    
                    
                </table>

            </section>
        </section>

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