<?php
require 'conexion.php';
session_start();

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
        <!-- **********************************************************************************************************************************************************
      TOP BAR CONTENT & NOTIFICATIONS
      *********************************************************************************************************************************************************** -->
        <!--header start-->
        <header class="header black-bg">
            <div class="sidebar-toggle-box">
                <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
            </div>
            <a href="index.php" class="logo"><b>Acces Perssonel</b></a>
            <div class="nav notify-row" id="top_menu">
                <!-- Notifications -->
                <ul class="nav top-menu">
                    <!-- Tasks Dropdown -->
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="fa fa-tasks"></i>
                            <span class="badge bg-theme">4</span>
                        </a>
                        <ul class="dropdown-menu extended tasks-bar">
                            <div class="notify-arrow notify-arrow-green"></div>
                            <li>
                                <p class="green">You have 4 pending tasks</p>
                            </li>
                            <!-- Example Task -->
                            <li>
                                <a href="#">
                                    <div class="task-info">
                                        <div class="desc">DashGum Admin Panel</div>
                                        <div class="percent">40%</div>
                                    </div>
                                    <div class="progress progress-striped">
                                        <div class="progress-bar progress-bar-success" style="width: 40%"></div>
                                    </div>
                                </a>
                            </li>
                            <!-- More tasks -->
                        </ul>
                    </li>
                    <!-- Messages Dropdown -->
                    <li id="header_inbox_bar" class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="fa fa-envelope-o"></i>
                            <span class="badge bg-theme">5</span>
                        </a>
                        <ul class="dropdown-menu extended inbox">
                            <div class="notify-arrow notify-arrow-green"></div>
                            <li>
                                <p class="green">You have 5 new messages</p>
                            </li>
                            <!-- Example Message -->
                            <li>
                                <a href="#">
                                    <span class="photo"><img alt="avatar" src="assets/img/ui-zac.jpg"></span>
                                    <span class="subject">
                                        <span class="from">Zac Snider</span>
                                        <span class="time">Just now</span>
                                    </span>
                                    <span class="message">Hi mate, how is everything?</span>
                                </a>
                            </li>
                            <!-- More messages -->
                        </ul>
                    </li>

                    <li id="header_profile_bar" class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="fa fa-user"></i>
                            <span class="badge bg-theme">1</span>
                        </a>
                        <ul class="dropdown-menu extended inbox">
                            <div class="notify-arrow notify-arrow-green"></div>
                            <li>
                                <p class="green">User Profile</p>
                            </li>
                            <li><a href="profile.php"><i class="fa fa-cogs"></i> Edit Profile</a></li>
                            <li><a href="settings.php"><i class="fa fa-cogs"></i> Settings</a></li>
                            <li><a href="login.php"><i class="fa fa-sign-out"></i> Logout</a></li>
                        </ul>
                    </li>

                </ul>
                <div class="top-menu">
                    <?php if (isset($_GET['login']) && $_GET['login'] == 'success' && isset($_GET['username'])): ?>
                        <ul class="nav pull-right top-menu">
                            <li>
                                <h3>Bienvenido, <?php echo htmlspecialchars($username); ?>!</h3>
                            </li>
                        </ul>
                    <?php elseif (isset($_GET['login']) && $_GET['login'] == 'error'): ?>
                        <ul class="nav pull-right top-menu">
                            <li>
                                <h3>Error en el inicio de sesión. Por favor, inténtelo de nuevo.</h3>
                            </li>
                        </ul>
                    <?php endif; ?>
                </div>

        </header>
        <!--header end-->

        <!-- **********************************************************************************************************************************************************
      MAIN SIDEBAR MENU
      *********************************************************************************************************************************************************** -->
        <!--sidebar start-->
        <aside>
            <div id="sidebar" class="nav-collapse ">
                <!-- sidebar menu start-->
                <ul class="sidebar-menu" id="nav-accordion">

                    <div id="sidebar" class="nav-collapse ">
                        <!-- sidebar menu start-->
                        <ul class="sidebar-menu" id="nav-accordion">

                            <p class="centered"><a href="profile.html"><img src="assets/img/ui-sam.jpg"
                                        class="img-circle" width="60"></a></p>
                            <h5 class="centered">Bienvenido, <?php echo $_SESSION['username']; ?>!</h5>

                            <li class="mt">
                                <a class="active" href="index.html">
                                    <i class="fa fa-dashboard"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>

                            <li class="sub-menu">
                                <a href="javascript:;">
                                    <i class="fa fa-desktop"></i>
                                    <span>UI Elements</span>
                                </a>
                                <ul class="sub">
                                    <li><a href="general.html">General</a></li>
                                    <li><a href="buttons.html">Buttons</a></li>
                                    <li><a href="panels.html">Panels</a></li>
                                </ul>
                            </li>


                            <li class="sub-menu">
                                <a href="javascript:;">
                                    <i class="fa fa-cogs"></i>
                                    <span>Components</span>
                                </a>
                                <ul class="sub">
                                    <li><a href="calendar.html">Calendar</a></li>
                                    <li><a href="gallery.html">Gallery</a></li>
                                    <li><a href="todo_list.html">Todo List</a></li>
                                </ul>
                            </li>
                            <li class="sub-menu">
                                <a href="javascript:;">
                                    <i class="bi bi-person-fill-gear"></i>
                                    <span>Administracion</span>
                                </a>
                                <ul class="sub">
                                    <li><a href="VerPlanilla.php"><i
                                                class="bi bi-journal-bookmark"></i><span>Planilla</span></a></li>
                                    <li><a href="MostrarUsuarios.php"><i
                                                class="bi bi-person-lines-fill"></i><span>Usuarios</span></a>
                                    </li>
                                    <li><a href="registrar_cambio_puesto.php">Registrar Cambio de Puesto</a></li>
                                    <li><a href="ver_historial_cambios.php">Historial de Cambios</a></li>

                                </ul>
                            </li>

                            </li>
                            <li class="sub-menu">
                                <a href="javascript:;">
                                    <i class="fa fa-tasks"></i>
                                    <span>Forms</span>
                                </a>
                                <ul class="sub">
                                    <li><a href="form_component.html">Form Components</a></li>
                                </ul>
                            </li>
                            <li class="sub-menu">
                                <a href="javascript:;">
                                    <i class="fa fa-th"></i>
                                    <span>Data Tables</span>
                                </a>
                                <ul class="sub">
                                    <li><a href="basic_table.html">Basic Table</a></li>
                                    <li><a href="responsive_table.html">Responsive Table</a></li>
                                </ul>
                            </li>
                            <li class="sub-menu">
                                <a href="javascript:;">
                                    <i class=" fa fa-bar-chart-o"></i>
                                    <span>Charts</span>
                                </a>
                                <ul class="sub">
                                    <li><a href="morris.html">Morris</a></li>
                                    <li><a href="chartjs.html">Chartjs</a></li>
                                </ul>
                            </li>

                        </ul>
                        <!-- sidebar menu end-->
                    </div>
        </aside>
        <!--sidebar end-->

        <!-- **********************************************************************************************************************************************************
      MAIN CONTENT
      *********************************************************************************************************************************************************** -->
        <!--main content start-->
        <style>
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
                        <h2 class="text-center mt-4">Bonos Aplicados</h2>
                        <table class="table table-bordered mt-4">
                            <thead>
                                <tr>
                                    <th>ID Bono</th>
                                    <th>Empleado</th>
                                    <th>Razón</th>
                                    <th>Monto</th>
                                    <th>Fecha Aplicación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id_bono']; ?></td>
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