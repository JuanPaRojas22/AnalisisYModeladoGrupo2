<?php
session_start();
require 'template.php';
require 'conexion.php';

//include "template.php";
// Verificar si el usuario está logueado
$id_usuario = $_SESSION['id_usuario'];

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}




?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horas Extras Registradas</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <title>Registar Horas Extra</title>

    <link href="assets/css/bootstrap.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="assets/css/zabuto_calendar.css">
    <link rel="stylesheet" type="text/css" href="assets/js/gritter/css/jquery.gritter.css" />
    <link rel="stylesheet" type="text/css" href="assets/lineicons/style.css">

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

        <div class="container">
            
                <a href="VerPlanilla.php" class="button"><i class="bi bi-arrow-return-left"></i>
                </a>
                <?php
                // Mostrar el mensaje de éxito o error
                if (isset($_SESSION['mensaje_exito'])) {
                    echo "<div class='alert alert-success'>" . $_SESSION['mensaje_exito'] . "</div>";
                    unset($_SESSION['mensaje_exito']);
                }

                if (isset($_SESSION['mensaje_error'])) {
                    echo "<div class='alert alert-danger'>" . $_SESSION['mensaje_error'] . "</div>";
                    unset($_SESSION['mensaje_error']);
                }
                ?>

                <h1>Listado de Horas Extras</h1>

                <table>
                    <thead>
                        <tr>

                            <th>Empleado</th>
                            <th>Usuario</th>
                            <th>fecha</th>
                            <th>Horas Extra Trabajadas</th>
                            <th>Monto Pago</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Mostrar los resultados de las horas extras
                        $sql_horas_extra = "SELECT u.username, u.nombre, u.apellido,h.fecha,h.tipo, 
                        SECOND(h.horas) AS horas, 
                        h.monto_pago, h.id_horas_extra, h.id_usuario 
                 FROM horas_extra h 
                 INNER JOIN usuario u ON h.id_usuario = u.id_usuario";
                        $result_horas_extra = $conn->query($sql_horas_extra);

                        if ($result_horas_extra->num_rows > 0) {
                            while ($row = $result_horas_extra->fetch_assoc()) {
                                echo "<tr>
                            <td>" . $row['nombre'], " ", $row['apellido'] . "</td>
                            <td>" . $row['username'] . "</td>
                            <td>" . $row['fecha'] . "</td>                       
                            <td>" . $row['horas'] . "</td>
                            <td>¢" . number_format($row['monto_pago'], 2) . "</td>
                            <td>" . $row['tipo'] . "</td>

                        </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='no-records'>No se encontraron registros de horas extras.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>


</body>
<style>
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 100%;
        margin: 100px auto;
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

    h3 {
        text-align: center;
        color: black;
        margin-bottom: 50px;
        margin-right: 10%;
        font-weight: bold;
    }

    .button {
        display: inline-block;
        background-color: #c9aa5f;
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
        margin-bottom: 20px;
        transition: background-color 0.3s;
    }



    .btn:hover {
        background-color: #c9aa5f;
    }

    .btn:active {
        background-color: #c9aa5f;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.6);
        
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
        background-color: #c9aa5f;
        color: #fff;
        text-align: center;
    }

    tr:hover {
        background-color: #c9aa5f;
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
        background-color: #c9aa5f;
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
<script src="assets/js/jquery.js"></script>
<script src="assets/js/jquery-1.8.3.min.js"></script>
<script src="assets/js/bootstrap.min.js"></script>
<script class="include" type="text/javascript" src="assets/js/jquery.dcjqaccordion.2.7.js"></script>
<script src="assets/js/jquery.scrollTo.min.js"></script>
<script src="assets/js/jquery.nicescroll.js" type="text/javascript"></script>
<script src="assets/js/jquery.sparkline.js"></script>


<!--common script for all pages-->
<script src="assets/js/common-scripts.js"></script>

<script type="text/javascript" src="assets/js/gritter/js/jquery.gritter.js"></script>
<script type="text/javascript" src="assets/js/gritter-conf.js"></script>

<!--script for this page-->
<script src="assets/js/sparkline-chart.js"></script>
<script src="assets/js/zabuto_calendar.js"></script>


</html>