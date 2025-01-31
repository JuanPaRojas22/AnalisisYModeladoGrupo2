<?php
// Incluye el archivo donde tienes definida la clase UsuarioDAOSImpl
require_once __DIR__ . '/Impl/UsuarioDAOSImpl.php';

// Instancia el DAO
$UsuarioDAO = new UsuarioDAOSImpl();

// Obtiene todos los usuarios
$id_departamento = isset($_GET['id_departamento']) ? $_GET['id_departamento'] : null;

$departmento = $UsuarioDAO->getAllDepartments();


// Obtiene los usuarios filtrados por departamento si es necesario
if ($id_departamento == 'all') {
    // Obtiene todos los usuarios sin filtrar
    $users = $UsuarioDAO->getAllUsers();
} elseif ($id_departamento) {
    // Si se seleccionó un departamento específico, obtiene los usuarios filtrados
    $users = $UsuarioDAO->getUsersByDepartment($id_departamento);
} else {
    // Si no se seleccionó ningún filtro, muestra todos los usuarios
    $users = $UsuarioDAO->getAllUsers();
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
                <h1>Lista de Usuarios</h1>

                <!-- Formulario para filtrar usuarios por departamento -->
                <form method="GET" action="MostrarUsuarios.php">
                    <div
                        style="display: flex; flex-direction: row; gap: 20px; margin-bottom: 20px; align-items: center;">
                        <!-- Select con las opciones de departamento -->
                        <select name="id_departamento" id="id_departamento"
                            style="padding: 5px; font-size: 14px; width: 250px;">
                            <option value="all">Seleccione un departamento</option>

                            <?php
                            // Recorre los departamentos y crea una opción para cada uno
                            foreach ($departmento as $department) {
                                $selected = (isset($id_departamento) && $id_departamento == $department['id_departamento']) ? 'selected' : '';
                                echo "<option value='{$department['id_departamento']}' {$selected}>{$department['Nombre']}</option>";
                            }
                            ?>
                        </select>

                        <!-- Botón para enviar el formulario -->
                        <button type="submit" class="btn btn-primary">Filtrar</button>
                    </div>
                </form>
                <!-- Enlace para generar el reporte PDF -->
                <div style="display: flex; flex-direction: row; gap: 20px; margin-bottom: 20px; align-items: center;">
                    <form action="generar_reporte.php" method="GET">
                        <select name="id_departamento" id="id_departamento" required
                            style="padding: 5px; font-size: 14px; width: 250px;">
                            <option>Seleccione un departamento</option>
                            <?php
                            // Recorre los departamentos y crea una opción para cada uno
                            foreach ($departmento as $department) {
                                $selected = (isset($id_departamento) && $id_departamento == $department['id_departamento']) ? 'selected' : '';
                                echo "<option value='{$department['id_departamento']}' {$selected}>{$department['Nombre']}</option>";
                            }
                            ?>
                            <!-- Agrega más opciones según tus departamentos -->
                        </select>
                        <button type="submit" class="btn btn-primary">Generar Reporte</button>
                    </form>
                </div>
                <div style="display: flex; flex-direction: row; gap: 20px; margin-bottom: 20px; align-items: center;">
                    <a href="aplicarBono.php" class="btn btn-success">Aplicar Bono</a>
                    <a href="verBono.php" class="btn btn-primary">Ver Bonos</a>
                </div>
                </div>
                <!-- Tabla de usuarios -->
                <div style="overflow-x: auto; margin-top: 20px;">
                    <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                        <thead>
                            <tr>
                                <th>Departamento</th>
                                <th>Rol</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Cargo</th>
                                <th style="width:15%">Correo Electrónico</th>
                                <th>Teléfono</th>
                                <th>Imagen</th>
                                <th>Sexo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Recorre los usuarios y los muestra en la tabla
                            foreach ($users as $user) {
                                echo "<tr>";
                                echo "<td><b>{$user['id_departamento']}</b></td>";
                                echo "<td><b>{$user['id_rol']}</b></td>";
                                echo "<td><b>{$user['nombre']}</b></td>";
                                echo "<td><b>{$user['apellido']}</b></td>";
                                echo "<td><b>{$user['cargo']}</b></td>";
                                echo "<td><b>{$user['correo_electronico']}</b></td>";
                                echo "<td><b>{$user['numero_telefonico']}</b></td>";
                                echo "<td><img src='{$user['direccion_imagen']}' alt='Imagen' style='width: 40px; height: 40px;'></td>";
                                echo "<td><b>{$user['sexo']}</b></td>";
                                echo "<td><b>{$user['id_estado']}</b></td>";
                                echo "<td>";
                                echo "<a href='editar.php?id={$user['id_usuario']}' class='btn btn-edit'>Editar</a>";
                                echo "<a href='eliminar.php?id={$user['id_usuario']}' class='btn btn-delete' onclick='return confirm(\"¿Estás seguro de eliminar este usuario?\")'>Eliminar</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
            </section>
        </section><!-- /MAIN CONTENT -->




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
    <script>
        $(document).ready(function () {
            $.ajax({
                url: '/Impl/UsuarioDAOSImpl.php', // El archivo PHP que recupera los usuarios
                type: 'GET',
                success: function (response) {
                    var users = JSON.parse(response);
                    var tableContent = '';
                    users.forEach(function (user) {
                        tableContent += `<tr>
              
                        </tr>`;
                    });
                    $('#userTable').html(tableContent); // Insertar las filas en la tabla
                },
                error: function (xhr, status, error) {
                    console.error('Error al cargar los usuarios:', error);
                }
            });
        });
    </script>



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