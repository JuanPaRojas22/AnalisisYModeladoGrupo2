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

<!-- (Tu código PHP hasta el <body> se mantiene igual) -->


    <section id="main-content">
        <section class="wrapper site-min-height">
            <!-- Botón para generar el PDF -->


            <div class="container">
    <h1>Solicitud de Vacación</h1>
    <a href="SolicitarVacacion.php" class="back-button"><i class="bi bi-arrow-return-left"></i> Volver</a>
    
    <div class="user-img">
        <?php if (!empty($user['direccion_imagen'])): ?>
            <img src="<?php echo htmlspecialchars($user['direccion_imagen']); ?>" alt="Imagen del usuario">
        <?php else: ?>
            <p>No hay imagen disponible</p>
        <?php endif; ?>
    </div>
    
    <div class="table-header">
        Detalles de la Solicitud
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
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 50%;
        max-width: 60%;
        margin: 50px auto;
        padding: 30px;
        background-color: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    h1 {
        text-align: center;
        color: #116B67;  /* Caribbean Current */
        margin-bottom: 20px;
        font-weight: bold;
    }

    .back-button {
        background-color: #116B67;  /* Caribbean Current */
        color: white;
        padding: 10px 20px;
        font-size: 16px;
        font-weight: bold;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s;
        margin-bottom: 20px;
    }

    .back-button:hover {
        background-color: #0E5D6A; /* Midnight Green */
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
        border: 3px solid #147964; /* Pine Green */
    }

    .user-details {
        margin-top: 20px;
        font-size: 16px;
        width: 100%;
        border-radius: 10px;
        padding: 10px;
    }

    .user-details th,
    .user-details td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
        color: #333;
    }

    .user-details th {
        background-color: #0E5D6A;  /* Midnight Green */
        color: #fff;
    }

    .user-details td {
        background-color: #f9f9f9;
    }

    .user-details tr:hover {
        background-color: #f1f1f1;
    }

    .user-details td,
    .user-details th {
        font-size: 14px;
    }

    .table-header {
        font-size: 18px;
        font-weight: bold;
        text-align: center;
        color: #116B67; /* Caribbean Current */
        margin-bottom: 10px;
    }
</style>



</body>

</html>