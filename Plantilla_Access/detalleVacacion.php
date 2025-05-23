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
    
</head>

<body>
         
    
    <section id="main-content">
        <section class="wrapper site-min-height">
            <!-- Botón para generar el PDF -->


            <div class="container">
                <h1>Solicitud de Vacación</h1>
                <div class="btn-container-wrapper">
                    <form method="get" action="vacaciones.php" accept-charset="UTF-8">
                        <input type="hidden" name="id_usuario" value="<?php echo $user['id_usuario']; ?>">
                        <button type="submit" class="btn-container"><i class="bi bi-arrow-return-left"></i></button>
                    </form>
                    <div>
                    
                    <a href="procesarVacacion.php?id=<?php echo $id_vacacion; ?>&accion=aprobar" class="btn-aprove" style="background-color: #137266; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">
    <i class="bi bi-check-circle-fill"></i>
</a>
<a href="procesarVacacion.php?id=<?php echo $id_vacacion; ?>&accion=rechazar" class="btn-decline" style="background-color: #C64A4A; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">
    <i class="bi bi-x-square-fill"></i>
</a>
            
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
                        <tr>
                            <th>Fecha Fin</th>
                            <td><?php echo htmlspecialchars($vacacion['fecha_fin']); ?></td>
                        </tr>
                        <tr>
                            <th>Días tomados</th>
                            <td><?php echo htmlspecialchars($vacacion['diasTomado']); ?></td>
                        </tr>
                        <tr>
                            <th>Razón</th>
                            <td><?php echo htmlspecialchars($vacacion['razon']); ?></td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td><?php echo htmlspecialchars($UsuarioDAO->getEstadoVacacionById($vacacion['id_estado_vacacion'])['descripcion']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php foreach ($historial_vacaciones as $historial_vacacion): ?>
                        <tr>
                            <th>Días restantes</th>
                            <td><?php echo htmlspecialchars($historial_vacacion['DiasRestantes']); ?></td>
                        </tr>
                    <?php endforeach; ?>
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
            border: 2px solid #116B67;
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
            background-color: #116B67;
            color: #fff;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        td {
            background-color: #116B67;
        }

        .btn-container-wrapper {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .btn-container {
            background-color: #147964;
            color: white;
            padding: 8px 12px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
           
        }
    </style>
        

</body>

</html>