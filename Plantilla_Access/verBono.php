<?php
require 'conexion.php';
session_start();
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <title>Ver Bonos</title>


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

    <section id="container">
        
        <style>
            
        .title-style {
            text-align: center;
            color: #333;
            margin-bottom: 50px;
            margin-right: 2%;
            font-weight: bold;
        }
                    body {
                        font-family: 'Ruda', sans-serif;
                        background-color: #f7f7f7;  /* Blanco cremoso */
                        margin: 0;
                        padding: 0;

                    }
                    

                    .container {
    width: 90%; /* Aumentar el tamaño del contenedor */
    margin: 50px auto; /* Centrar el contenedor */
    padding: 20px;
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Sombra alrededor del contenedor */
}


                    h1 {
                        text-align: center;
                        color: #333;
                        margin-bottom: 50px;
                        margin-right: 2%;
                        font-weight: bold;

                    }
                    h2{
                        text-align: center;
            color: #333;
            margin-bottom: 50px;
            margin-right: 2%;
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
                        background-color: #0C536C;
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
                        background-color: #0C536C;
                    }
                    

                    .btn:active {
                        background-color: #0C536C;
                        box-shadow: 0 4px 10px rgb(254, 254, 254);

                    }

                    table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    font-size: 12px; /* Reducir el tamaño de la fuente */
}

th, td {
    padding: 8px; /* Reducir el espaciado de las celdas */
    text-align: center;
    font-size: 12px; /* Reducir el tamaño de la fuente */
    color: #555;
    border-bottom: 1px solid #ddd;
}

th {
    background-color: #116B67;
    color: white;
}
th:first-child {
    border-radius: 8px 0 0 0; /* Redondear la esquina superior izquierda */
}
th:last-child {
    border-radius: 0 8px 0 0; /* Redondear la esquina superior derecha */
}


tr:hover {
    background-color: #f7f7f7;
}

                   td {
                        background-color: #f7f7f7;
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
                        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.6);

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
                </style>
        <section id="main-content">
            <section class="wrapper site-min-height">

                <?php

                $query = "SELECT Bonos.id_bono, Usuario.nombre, Bonos.razon, Bonos.monto_total, Bonos.fecha_aplicacion 
          FROM Bonos 
          INNER JOIN Usuario ON Bonos.id_usuario = Usuario.id_usuario";
                $result = $conn->query($query);
                ?>

                <!DOCTYPE html>
                <html lang="es">

                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Bonos Aplicados</title>
                    <link href="assets/css/bootstrap.css" rel="stylesheet">
                    <link href="assets/css/style.css" rel="stylesheet">
                </head>

                <body>
                    <div class="container">
                    <h2 style="text-align: center; color: #333; margin-bottom: 50px; margin-right: 2%; font-weight: bold;">Bonos Aplicados</h2>
                        <table class="table table-bordered mt-4">
                            <thead>
                                <tr>
                  }
                                    <th>Empleado</th>
                                    <th>Razón</th>
                                    <th>Monto</th>
                                    <th>Fecha Aplicación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                       
                                        <td><?php echo $row['nombre']; ?></td>
                                        <td><?php echo $row['razon']; ?></td>
                                        <td>₡<?php echo number_format($row['monto_total'], 2); ?></td>
                                        <td><?php echo $row['fecha_aplicacion']; ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>

                        <div class="text-center mt-4">
                            <a href="VerPlanilla.php" class="btn btn-secondary">Volver</a>
                        </div>
                    </div>

                    <script src="assets/js/jquery.js"></script>
                    <script src="assets/js/bootstrap.min.js"></script>
                </body>

                </html>

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