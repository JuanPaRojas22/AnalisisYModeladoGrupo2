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

    <title>Ver Deducciones</title>

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

    <section id="container">
        <!-- **********************************************************************************************************************************************************
      TOP BAR CONTENT & NOTIFICATIONS
      *********************************************************************************************************************************************************** -->
        <!--header start-->
        <header class="header black-bg">
            <div class="sidebar-toggle-box">
                <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
            </div>
            <!--logo start-->
            <a href="index.php" class="logo"><b>DASHGUM FREE</b></a>
            <!--logo end-->
            <div class="nav notify-row" id="top_menu">
                <!--  notification start -->
                <ul class="nav top-menu">
                    <!-- settings start -->
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="index.php#">
                            <i class="fa fa-tasks"></i>
                            <span class="badge bg-theme">4</span>
                        </a>
                        <ul class="dropdown-menu extended tasks-bar">
                            <div class="notify-arrow notify-arrow-green"></div>
                            <li>
                                <p class="green">You have 4 pending tasks</p>
                            </li>
                            <li>
                                <a href="index.php#">
                                    <div class="task-info">
                                        <div class="desc">DashGum Admin Panel</div>
                                        <div class="percent">40%</div>
                                    </div>
                                    <div class="progress progress-striped">
                                        <div class="progress-bar progress-bar-success" role="progressbar"
                                            aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width: 40%">
                                            <span class="sr-only">40% Complete (success)</span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a href="index.php#">
                                    <div class="task-info">
                                        <div class="desc">Database Update</div>
                                        <div class="percent">60%</div>
                                    </div>
                                    <div class="progress progress-striped">
                                        <div class="progress-bar progress-bar-warning" role="progressbar"
                                            aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%">
                                            <span class="sr-only">60% Complete (warning)</span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a href="index.php#">
                                    <div class="task-info">
                                        <div class="desc">Product Development</div>
                                        <div class="percent">80%</div>
                                    </div>
                                    <div class="progress progress-striped">
                                        <div class="progress-bar progress-bar-info" role="progressbar"
                                            aria-valuenow="80" aria-valuemin="0" aria-valuemax="100" style="width: 80%">
                                            <span class="sr-only">80% Complete</span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a href="index.php#">
                                    <div class="task-info">
                                        <div class="desc">Payments Sent</div>
                                        <div class="percent">70%</div>
                                    </div>
                                    <div class="progress progress-striped">
                                        <div class="progress-bar progress-bar-danger" role="progressbar"
                                            aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width: 70%">
                                            <span class="sr-only">70% Complete (Important)</span>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li class="external">
                                <a href="#">See All Tasks</a>
                            </li>
                        </ul>
                    </li>
                    <!-- settings end -->
                    <!-- inbox dropdown start-->
                    <li id="header_inbox_bar" class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="index.php#">
                            <i class="fa fa-envelope-o"></i>
                            <span class="badge bg-theme">5</span>
                        </a>
                        <ul class="dropdown-menu extended inbox">
                            <div class="notify-arrow notify-arrow-green"></div>
                            <li>
                                <p class="green">You have 5 new messages</p>
                            </li>
                            <li>
                                <a href="index.php#">
                                    <span class="photo"><img alt="avatar" src="assets/img/ui-zac.jpg"></span>
                                    <span class="subject">
                                        <span class="from">Zac Snider</span>
                                        <span class="time">Just now</span>
                                    </span>
                                    <span class="message">
                                        Hi mate, how is everything?
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php#">
                                    <span class="photo"><img alt="avatar" src="assets/img/ui-divya.jpg"></span>
                                    <span class="subject">
                                        <span class="from">Divya Manian</span>
                                        <span class="time">40 mins.</span>
                                    </span>
                                    <span class="message">
                                        Hi, I need your help with this.
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php#">
                                    <span class="photo"><img alt="avatar" src="assets/img/ui-danro.jpg"></span>
                                    <span class="subject">
                                        <span class="from">Dan Rogers</span>
                                        <span class="time">2 hrs.</span>
                                    </span>
                                    <span class="message">
                                        Love your new Dashboard.
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php#">
                                    <span class="photo"><img alt="avatar" src="assets/img/ui-sherman.jpg"></span>
                                    <span class="subject">
                                        <span class="from">Dj Sherman</span>
                                        <span class="time">4 hrs.</span>
                                    </span>
                                    <span class="message">
                                        Please, answer asap.
                                    </span>
                                </a>
                            </li>
                            <li>
                                <a href="index.php#">See all messages</a>
                            </li>
                        </ul>
                    </li>
                    <!-- inbox dropdown end -->
                </ul>
                <!--  notification end -->
            </div>
            <div class="top-menu">
                <ul class="nav pull-right top-menu">
                    <li><a class="logout" href="login.php">Logout</a></li>
                </ul>
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

                    <p class="centered"><a href="profile.html"><img src="assets/img/ui-sam.jpg" class="img-circle"
                                width="60"></a></p>
                    <h5 class="centered">Marcel Newman</h5>

                    <li class="mt">
                        <a href="index.php">
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
                        <a class="active" href="javascript:;">
                            <i class="fa fa-book"></i>
                            <span>Extra Pages</span>
                        </a>
                        <ul class="sub">
                            <li class="active"><a href="blank.html">Blank Page</a></li>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="lock_screen.html">Lock Screen</a></li>
                        </ul>
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
        <section id="main-content">
            <section class="wrapper site-min-height">
                <h1>Deducciones</h1>
                <!-- /MAIN CONTENT -->
                <?php
                // Obtener el ID de usuario logueado
                $id_usuario_logueado = $_SESSION['id_usuario'];

                // Obtener el ID de usuario seleccionado (si existe)
                $id_usuario_seleccionado = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : null;

                // Si no se selecciona un usuario, mostramos todos
                if ($id_usuario_seleccionado === null || $id_usuario_seleccionado === '') {
                    $sql_deducciones = "
        SELECT 
            d.id_deduccion, 
            d.id_usuario, 
            u.nombre, 
            u.apellido, 
            d.razon, 
            d.deudor, 
            d.concepto, 
            d.lugar, 
            d.deuda_total, 
            d.aportes, 
            d.saldo_pendiente, 
            d.saldo_pendiente_dolares, 
            d.fechacreacion
        FROM 
            Deducciones d
        INNER JOIN 
            Usuario u ON d.id_usuario = u.id_usuario";
                } else {
                    // Si se selecciona un usuario específico
                    $sql_deducciones = "
        SELECT 
            d.id_deduccion, 
            d.id_usuario, 
            u.nombre, 
            u.apellido, 
            d.razon, 
            d.deudor, 
            d.concepto, 
            d.lugar, 
            d.deuda_total, 
            d.aportes, 
            d.saldo_pendiente, 
            d.saldo_pendiente_dolares, 
            d.fechacreacion
        FROM 
            Deducciones d
        INNER JOIN 
            Usuario u ON d.id_usuario = u.id_usuario
        WHERE 
            d.id_usuario = ?";
                }

                // Preparar la consulta
                $stmt_deducciones = $conn->prepare($sql_deducciones);

                // Si se selecciona un usuario específico, bind_param para el id_usuario
                if ($id_usuario_seleccionado !== null && $id_usuario_seleccionado !== '') {
                    $stmt_deducciones->bind_param("i", $id_usuario_seleccionado);
                }

                // Ejecutar la consulta
                $stmt_deducciones->execute();
                $result_deducciones = $stmt_deducciones->get_result();

                // Obtener todos los usuarios para el dropdown
                $sql_usuarios = "SELECT id_usuario, nombre, apellido FROM Usuario";
                $result_usuarios = $conn->query($sql_usuarios);
                ?>

                <!DOCTYPE html>
                <html lang="es">

                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Listado de Deducciones</title>
                    <style>
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            table-layout: fixed;
                        }

                        th,
                        td {
                            border: 1px solid #ddd;
                            padding: 8px;
                            text-align: left;
                        }

                        th {
                            background-color: #f2f2f2;
                        }

                        select {
                            padding: 8px;
                            margin: 20px 0;
                        }
                    </style>
                </head>

                <body>
                    <h1>Listado de Deducciones</h1>

                    <!-- Formulario para seleccionar un usuario -->
                    <form method="POST" action="">
                        <label for="id_usuario">Seleccionar usuario:</label>
                        <select name="id_usuario" id="id_usuario">
                            <option value="">Ver todos</option>
                            <?php while ($row_usuario = $result_usuarios->fetch_assoc()) { ?>
                                <option value="<?= $row_usuario['id_usuario']; ?>"
                                    <?= ($id_usuario_seleccionado == $row_usuario['id_usuario']) ? 'selected' : ''; ?>>
                                    <?= $row_usuario['nombre'] . " " . $row_usuario['apellido']; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <button type="submit">Filtrar</button>
                    </form>

                    <!-- Mostrar los resultados -->
                    <div style="overflow-x: auto; margin-top: 20px;">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID Deducción</th>
                                    <th>Nombre del Usuario</th>
                                    <th>Razón</th>
                                    <th>Deudor</th>
                                    <th>Concepto</th>
                                    <th>Lugar</th>
                                    <th>Deuda Total</th>
                                    <th>Aportes</th>
                                    <th>Saldo Pendiente</th>
                                    <th>Saldo Pendiente (USD)</th>
                                    <th>Fecha de Creación</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result_deducciones->num_rows > 0) {
                                    while ($row_deduccion = $result_deducciones->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row_deduccion['id_deduccion'] . "</td>";
                                        echo "<td>" . $row_deduccion['nombre'] . " " . $row_deduccion['apellido'] . "</td>";
                                        echo "<td>" . $row_deduccion['razon'] . "</td>";
                                        echo "<td>" . $row_deduccion['deudor'] . "</td>";
                                        echo "<td>" . $row_deduccion['concepto'] . "</td>";
                                        echo "<td>" . $row_deduccion['lugar'] . "</td>";
                                        echo "<td>" . $row_deduccion['deuda_total'] . "</td>";
                                        echo "<td>" . $row_deduccion['aportes'] . "</td>";
                                        echo "<td>" . $row_deduccion['saldo_pendiente'] . "</td>";
                                        echo "<td>" . $row_deduccion['saldo_pendiente_dolares'] . "</td>";
                                        echo "<td>" . $row_deduccion['fechacreacion'] . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='11'>No hay deducciones para este usuario.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group text-center mt-3">

                        <a href="VerPlanilla.php" class="btn btn-secondary">Volver</a>
                    </div>

                    <?php
                    // Cerrar la conexión
                    $conn->close();
                    ?>
                </body>

                </html>

            </section>
        </section>
        <!--main content end-->

        <!--footer start-->
        <footer class="site-footer">
            <div class="text-center">
                2014 - Alvarez.is
                <a href="blank.html#" class="go-top">
                    <i class="fa fa-angle-up"></i>
                </a>
            </div>
        </footer>
        <!--footer end-->
    </section>

    <!-- js placed at the end of the document so the pages load faster -->
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/jquery-ui-1.9.2.custom.min.js"></script>
    <script src="assets/js/jquery.ui.touch-punch.min.js"></script>
    <script class="include" type="text/javascript" src="assets/js/jquery.dcjqaccordion.2.7.js"></script>
    <script src="assets/js/jquery.scrollTo.min.js"></script>
    <script src="assets/js/jquery.nicescroll.js" type="text/javascript"></script>




    <!--common script for all pages-->
    <script src="assets/js/common-scripts.js"></script>

    <!--script for this page-->

    <script>
        //custom select box

        $(function () {
            $('select.styled').customSelect();
        });

    </script>

</body>
<style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f4f4f9;
        margin: 1;
        padding: 1;
    }

    h1 {
        text-align: center;
        color: #333;
        margin-top: 30px;
    }

    table {
        width: 80%;
        margin: 10px auto;
        border-collapse: collapse;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        background-color: #fff;
    }

    th {
        background-color: rgb(102, 139, 187);
        color: white;
        padding: 5px;
        text-align: center;
    }

    td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }

    tr:hover {
        background-color: #e9f7fc;
    }

    td img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        transition: transform 0.3s ease;
    }

    td img:hover {
        transform: scale(1.1);
    }

    /* Botones */
    .btn {
        padding: 6px 12px;
        margin: 0 4px;
        cursor: pointer;
        border-radius: 4px;
        text-decoration: none;
    }

    .btn-edit {
        background-color: #5D9CEC;
        color: white;
    }

    .btn-delete {
        background-color: #f44336;
        color: white;
    }
</style>

</html>