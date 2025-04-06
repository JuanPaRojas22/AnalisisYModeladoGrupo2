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

<!-- (Tu código PHP hasta el <body> se mantiene igual) -->
<body>
    <div class="container">
        <h1>Solicitud de Vacación</h1>

        <div class="user-img">
            <?php if (!empty($user['direccion_imagen'])): ?>
                <img src="<?php echo htmlspecialchars($user['direccion_imagen']); ?>" alt="Imagen del usuario">
            <?php else: ?>
                <p>No hay imagen disponible</p>
            <?php endif; ?>
        </div>

        <div class="card-grid">
            <div class="info-card"><h4>Nombre</h4><p><?php echo htmlspecialchars($user['nombre']); ?></p></div>
            <div class="info-card"><h4>Apellido</h4><p><?php echo htmlspecialchars($user['apellido']); ?></p></div>

            <?php foreach ($vacaciones as $vacacion): ?>
                <div class="info-card"><h4>Fecha de inicio</h4><p><?php echo htmlspecialchars($vacacion['fecha_inicio']); ?></p></div>
                <div class="info-card"><h4>Fecha Fin</h4><p><?php echo htmlspecialchars($vacacion['fecha_fin']); ?></p></div>
                <div class="info-card"><h4>Días tomados</h4><p><?php echo htmlspecialchars($vacacion['diasTomado']); ?></p></div>
                <div class="info-card"><h4>Razón</h4><p><?php echo htmlspecialchars($vacacion['razon']); ?></p></div>
                <div class="info-card"><h4>Estado</h4><p><?php echo htmlspecialchars($UsuarioDAO->getEstadoVacacionById($vacacion['id_estado_vacacion'])['descripcion']); ?></p></div>
            <?php endforeach; ?>

            <?php foreach ($historial_vacaciones as $historial_vacacion): ?>
                <div class="info-card"><h4>Días restantes</h4><p><?php echo htmlspecialchars($historial_vacacion['DiasRestantes']); ?></p></div>
            <?php endforeach; ?>
        </div>

        <div class="btn-container">
            <a href="SolicitarVacacion.php" class="btn">Volver</a>
        </div>
    </div>

    <style>
        body {
            font-family: 'Ruda', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 1200px;
            margin: 50px auto 50px 245px; /* Adjusted left margin to move the container to the right */
            padding: 40px;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        h1 {
            text-align: center;
            color: #333;
            font-weight: bold;
            margin: 0 auto 40px;
            margin-bottom: 40px;
            display: block;
            width: fit-content;
        }

        .user-img {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .user-img img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #c9aa5f;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
        }

        .info-card {
            background-color: #fdfdfd;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-top: 4px solid #c9aa5f;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.2);
        }

        .info-card h4 {
            margin: 0 0 10px;
            font-size: 18px;
            color: #444;
        }

        .info-card p {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .btn-container {
            margin-top: 40px;
            display: flex;
            justify-content: center;
        }

        .btn {
            background-color: #c9aa5f;
            color: white;
            padding: 12px 30px;
            font-size: 18px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
        }

        .btn:hover {
            background-color: #b1954d;
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 20px;
            }
        }
    </style>
</body>

</html>