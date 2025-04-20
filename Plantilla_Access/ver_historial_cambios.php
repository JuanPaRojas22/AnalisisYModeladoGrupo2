<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

include "template.php";

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">

    <title>Historial</title>

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
        td, div {
            color: black !important;
        }
    </style>
</head>

<body>

    <section id="container">
        
        <!--sidebar end-->

        <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
        <!--main content start-->
<section id="main-content">
    <section class="wrapper site-min-height">
        <h1>Historial</h1>
        <!-- /MAIN CONTENT -->
        <?php
        // Verificar si el usuario está logueado
// Conexión a la base de datos
$host = "localhost"; 
$usuario = "root"; 
$clave = ""; 
$bd = "gestionempleados"; 
$conn = new mysqli($host, $usuario, $clave, $bd);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Consulta para obtener el historial de cambios
$sql = "SELECT hc.id_historial, u.nombre AS nombre_usuario, hc.puesto_anterior, hc.nuevo_puesto, hc.fecha_cambio, hc.motivo, hc.fechacreacion, hc.sueldo_anterior, hc.sueldo_nuevo
        FROM Historial_Cargos hc
        JOIN Usuario u ON hc.id_usuario = u.id_usuario
        ORDER BY hc.fecha_cambio DESC";

$result = $conn->query($sql);

?>

<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Cambios de Puesto</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            color: black !important;
        }
        .btn {
            display: inline-block;
            background-color: #116B67;
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #147665;
        }

        .btn:active {
            background-color: #147665;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 12px;
            text-align: left;
            font-size: 16px;
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
            color: black !important;
        }

        .no-records {
            text-align: center;
            font-style: italic;
            color: #888;
        }
        
    </style>
</head>
<body>
    <div class="container">
        <h1>Historial de Cambios de Puesto</h1>

        <a href="registrar_cambio_puesto.php" class="btn">
            Ir al Formulario de Cambio de Puesto
        </a>
        <!-- Mostrar tabla con los cambios de puesto -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Puesto Anterior</th>
                    <th>Nuevo Puesto</th>
                    <th>Fecha de Cambio</th>
                    <th>Motivo</th>
                    <th>Fecha de Creación</th>
                    <th>Sueldo Anterior</th>
                    <th>Sueldo Nuevo</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Mostrar los resultados de la consulta
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row['id_historial'] . "</td>
                                <td>" . $row['nombre_usuario'] . "</td>
                                <td>" . $row['puesto_anterior'] . "</td>
                                <td>" . $row['nuevo_puesto'] . "</td>
                                <td>" . $row['fecha_cambio'] . "</td>
                                <td>" . $row['motivo'] . "</td>
                                <td>" . $row['fechacreacion'] . "</td>
                                <td>" . $row['sueldo_anterior'] . "</td>
                                <td>" . $row['sueldo_nuevo'] . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='9' class='no-records'>No se encontraron registros.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
// Cerrar la conexión
$conn->close();
?>
