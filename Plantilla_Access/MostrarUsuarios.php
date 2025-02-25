<?php

session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">


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
                                                class="bi bi-person-lines-fill"></i><span>Usuarios</span></a></li>
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
        <section id="main-content">
            <section class="wrapper site-min-height">
                <!-- Tabla de usuarios -->
                <div class="container">
                    <h1>Listado de Usuarios</h1>
                    <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                        <div style="display: flex; justify-content: space-between; gap: 10px; width: 100%;">

                            <!-- Formulario con select alineado a la izquierda -->
                            <form action="generar_reporte.php" method="GET"
                                style="display: flex; align-items: center; gap: 10px; flex: 2;">
                                <div style="display: flex; align-items: center;">
                                    <select class="form-select" name="id_departamento" id="id_departamento"
                                        style="font-size: 14px; width: 240px; height: 20px;" require>
                                        <option>Seleccione un departamento</option>
                                        <?php
                                        foreach ($departmento as $department) {
                                            $selected = (isset($id_departamento) && $id_departamento == $department['id_departamento']) ? 'selected' : '';
                                            echo "<option value='{$department['id_departamento']}' {$selected}>{$department['Nombre']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div style="display: flex;">
                                    <button class="btn-select" style="font-size: 2rem; color: black;">
                                        <i class="bi bi-filetype-pdf"></i>
                                    </button>
                                </div>
                            </form>

                            <!-- Formulario con select alineado a la derecha -->
                            <form method="GET" action="MostrarUsuarios.php"
                                style="display: flex; align-items: center; margin-left: 450px;">
                                <div style="display: flex; align-items: center;">
                                    <select name="id_departamento" id="id_departamento"
                                        style="font-size: 14px; width: 240px; height: 20px;">
                                        <option value="all">Seleccione un departamento</option>
                                        <?php
                                        foreach ($departmento as $department) {
                                            $selected = (isset($id_departamento) && $id_departamento == $department['id_departamento']) ? 'selected' : '';
                                            echo "<option value='{$department['id_departamento']}' {$selected}>{$department['Nombre']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div style="display: flex; align-items: center;">
                                    <button class="btn-select" style="font-size: 1.5rem; color: black;">
                                        <i class="bi bi-funnel-fill"></i>
                                    </button>
                                </div>
                            </form>

                        </div>
                </div>
                <thead>
                    <tr>
                        <th style="width:10%">Departamento</th>
                        <th style="width:10%">Rol</th>
                        <th style="width:10%">Nombre</th>
                        <th style="width:15%">Apellido</th>
                        <th style="width:10%">Ocupacion</th>
                        <th style="width:10%">Nacionalidad</th>
                        <th style="width:20%">Correo Electrónico</th>
                        <th style="width:11%">Teléfono</th>
                        <th style="width:10%">Imagen</th>
                        <th style="width:10%">Sexo</th>
                        <th style="width:10%">Estado</th>
                        <th style="width:10%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Recorre los usuarios y los muestra en la tabla
                    foreach ($users as $user) {
                        echo "<tr>";
                        echo "<td><b>{$user['departamento_nombre']}</b></td>";
                        echo "<td><b>{$user['rol_nombre']}</b></td>";
                        echo "<td><b>{$user['nombre']}</b></td>";
                        echo "<td><b>{$user['apellido']}</b></td>";
                        echo "<td><b>{$user['Nombre_Ocupacion']}</b></td>";
                        echo "<td><b>{$user['Nombre_Pais']}</b></td>";
                        echo "<td><b>{$user['correo_electronico']}</b></td>";
                        echo "<td><b>{$user['numero_telefonico']}</b></td>";
                        //Carga la imagen del usuario
                        echo "<td><img src='{$user['direccion_imagen']}' alt='Imagen' style='width: 40px; height: 40px;'></td>";
                        echo "<td><b>{$user['sexo']}</b></td>";
                        echo "<td><b>{$user['estado']}</b></td>";
                        echo "<td>";
                        echo "<div class='d-flex flex-column gap-2'>  
                                <a href='editar.php?id={$user['id_usuario']}' class='btn btn-primary' style='font-size: 2.5rem;'>
                                    <i class='bi bi-pencil-square'></i> 
                                </a>
                                <a href='detalle.php?id={$user['id_usuario']}' class='btn btn-success' style='font-size: 2.5rem;'>
                                    <i class='bi bi-file-earmark-person'></i> 
                                </a>
                                <a href='eliminar.php?id={$user['id_usuario']}' class='btn btn-danger' style='font-size: 2.5rem;' onclick='return confirm(\"¿Estás seguro de eliminar este usuario?\")'>
                                    <i class='bi bi-trash'></i>
                                </a>
                            </div>";

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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

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
        font-family: 'Ruda', sans-serif;
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
        margin-bottom: 50px;
        margin-right: 10%;
        font-weight: bold;
    }



    .btn-select {
        background-color: #c9aa5f;
        color: white;
        padding: 10px 20px;
        font-size: 25px;
        text-align: center;
        text-decoration: none;
        border-radius: 10px;
        transition: background-color 0.3s;
    }

    .btn-select:hover {
        background-color: #b8a15a;
    }

    .btn-select:active {
        background-color: #c9aa5f;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        border-radius: 8px;
        overflow: hidden;
        table-layout: fixed;

    }

    th,
    td {
        padding: 12px;
        text-align: center;
        font-size: 16px;
        color: #555;
        border-bottom: 1px solid #ddd;
        word-wrap: break-word;
        min-width: 100px;

        height: 50px;

    }

    th {
        background-color: #c9aa5f;
        color: #fff;
        height: 50px;
        /* Mantén el alto consistente con las celdas */

    }

    tr:hover {
        background-color: #f1f1f1;
    }

    td {
        background-color: #f9f9f9;
    }

    .no-records {
        text-align: center;
        font-style: italic;
        color: #888;
    }
</style>

</html>