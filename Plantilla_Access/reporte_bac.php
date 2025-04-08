<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "gestionempleados");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
session_start();
include 'template.php';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Dashboard">
    <meta name="keyword" content="Dashboard, Bootstrap, Admin, Template, Theme, Responsive, Fluid, Retina">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <title>Reporte BAC</title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
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

    
        <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
        <!--main content start-->
        <section id="main-content">
            <section class="wrapper site-min-height">

                <?php
                // Consulta para obtener los datos
                $sql = "SELECT id_reporte_bac, id_usuario, cedula_bac, salario_neto, fecha_generacion, link_archivo FROM
                Reporte_Bac";
                $resultado = $conexion->query($sql);
                ?>

                <!DOCTYPE html>
                <html lang="es">

                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Reporte BAC</title>

                </head>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Ruda:wght@400;700&display=swap');

                    body {
                        font-family: 'Ruda', sans-serif;
                        background-color: #f7f7f7;
                        margin: 0;
                        padding: 20px;
                    }

                    .container {
                        max-width: 1000px;
                        margin: auto;
                        background: white;
                        padding: 20px;
                        border-radius: 10px;
                        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                        text-align: center;
                    }

                    h1 {
                        color: #333;
                        font-weight: bold;
                        margin-bottom: 20px;
                    }

                    .btn-export {
                        display: inline-block;
                        background-color: #c9aa5f;
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

                    .btn-export:hover {
                        background-color: #b5935b;
                    }

                    .table-container {
                        overflow-x: auto;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 10px;
                    }

                    th,
                    td {
                        padding: 12px;
                        text-align: center;
                        border-bottom: 1px solid #ddd;
                    }

                    th {
                        background-color: #c9aa5f;
                        color: white;
                    }

                    tr:hover {
                        background-color: #f1f1f1;
                    }

                    .btn-more {
                        background-color: #c9aa5f;
                        color: white;
                        padding: 6px 12px;
                        font-size: 14px;
                        font-weight: bold;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        transition: background-color 0.3s;
                    }

                    .btn-more:hover {
                        background-color: #b5935b;
                    }

                    /* Estilos del detalle oculto */
                    .details {
                        display: none;
                        background: #fff7e6;
                        padding: 15px;
                        border-radius: 8px;
                        margin-top: 5px;
                        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
                        transition: all 0.3s ease-in-out;
                    }

                    .details p {
                        margin: 5px 0;
                        font-size: 15px;
                        text-align: left;
                    }

                    /* Animación para mostrar detalles */
                    .details.show {
                        display: block;
                        animation: fadeIn 0.3s ease-in-out;
                    }

                    @keyframes fadeIn {
                        from {
                            opacity: 0;
                            transform: translateY(-5px);
                        }

                        to {
                            opacity: 1;
                            transform: translateY(0);
                        }
                    }
                </style>

                <body>
                    <div class="container mt-4">
                        <h1 class="text-center">Reporte BAC</h1>
                        <a href="exportar_reporte_bac.php" class="btn-export">
        📥 Descargar Reporte
    </a>
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>ID Reporte BAC</th>
                                            <th>ID Usuario</th>
                                            <th>Cedula BAC</th>
                                            <th>Salario Neto</th>
                                            <th>Fecha de Generacion</th>
                                            <th>Link Archivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($fila = $resultado->fetch_assoc()) { ?>
                                            <tr>
                                                <td><?php echo $fila['id_reporte_bac']; ?></td>
                                                <td><?php echo $fila['id_usuario']; ?></td>
                                                <td><?php echo $fila['cedula_bac']; ?></td>
                                                <td><?php echo number_format($fila['salario_neto'], 2); ?></td>
                                                <td><?php echo $fila['fecha_generacion']; ?></td>
                                                <td><a href="<?php echo $fila['link_archivo']; ?>" target="_blank">Ver
                                                        Archivo</a></td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                             
                            </div>
                        </div>
                    </div>
                </body>

                </html>

                <?php
                // Cerrar conexión
                $conexion->close();
                ?>
                <script>
                    // Función para abrir el modal
                    function abrirModal(modalId) {
                        document.getElementById(modalId).style.display = 'flex';
                    }

                    // Función para cerrar el modal
                    function cerrarModal(modalId) {
                        document.getElementById(modalId).style.display = 'none';
                    }
                </script>
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

                <script type="text/javascript">
                    $(document).ready(function () {
                        var unique_id = $.gritter.add({
                            // (string | mandatory) the heading of the notification
                            title: 'Welcome to Dashgum!',
                            // (string | mandatory) the text inside the notification
                            text: 'Hover me to enable the Close Button. You can hide the left sidebar clicking on the button next to the logo. Free version for <a href="http://blacktie.co" target="_blank" style="color:#ffd777">BlackTie.co</a>.',
                            // (string | optional) the image to display on the left
                            image: 'assets/img/ui-sam.jpg',
                            // (bool | optional) if you want it to fade out on its own or just sit there
                            sticky: true,
                            // (int | optional) the time you want it to be alive for before fading out
                            time: '',
                            // (string | optional) the class name you want to apply to that specific message
                            class_name: 'my-sticky-class'
                        });

                        return false;
                    });
                </script>

                <script type="application/javascript">
                    $(document).ready(function () {
                        $("#date-popover").popover({ html: true, trigger: "manual" });
                        $("#date-popover").hide();
                        $("#date-popover").click(function (e) {
                            $(this).hide();
                        });

                        $("#my-calendar").zabuto_calendar({
                            action: function () {
                                return myDateFunction(this.id, false);
                            },
                            action_nav: function () {
                                return myNavFunction(this.id);
                            },
                            ajax: {
                                url: "show_data.php?action=1",
                                modal: true
                            },
                            legend: [
                                { type: "text", label: "Special event", badge: "00" },
                                { type: "block", label: "Regular event", }
                            ]
                        });
                    });


                    function myNavFunction(id) {
                        $("#date-popover").hide();
                        var nav = $("#" + id).data("navigation");
                        var to = $("#" + id).data("to");
                        console.log('nav ' + nav + ' to: ' + to.month + '/' + to.year);
                    }
                </script>