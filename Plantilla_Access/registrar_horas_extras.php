<?php
// Establecer zona horaria
date_default_timezone_set('America/Costa_Rica');

require 'conexion.php';
session_start();

if (isset($_POST['calcular_horas_extra'])) {
    $id_usuario = $_SESSION['id_usuario'] ?? null;

    // Validación de sesión
    if (!$id_usuario) {
        die("Error: Usuario no autenticado.");
    }

    // Obtener fecha actual
    $fecha_hora_extra = date("Y-m-d");

    // **Verificar si ya se registró horas extras hoy**
    $query_verificar = "SELECT COUNT(*) FROM horas_extra WHERE id_usuario = ? AND fecha = ?";
    $stmt_verificar = $conn->prepare($query_verificar);
    $stmt_verificar->bind_param("is", $id_usuario, $fecha_hora_extra);
    $stmt_verificar->execute();
    $stmt_verificar->bind_result($existe_registro);
    $stmt_verificar->fetch();
    $stmt_verificar->close();

    if ($existe_registro > 0) {
        header("Location: registrar_horas_extras.php?hora_registrada=error");
        exit();
    }

    // Obtener tipo de día seleccionado por el usuario
    $tipo_dia = isset($_POST['tipo_dia']) ? $_POST['tipo_dia'] : 'Regular'; // Default: Regular

    // Obtener fecha actual y día de la semana
    $fecha_hora_extra = date("Y-m-d");
    $dia_semana = date("N", strtotime($fecha_hora_extra)); // 1 = Lunes, 7 = Domingo

    // Consultar si es un feriado en la base de datos
    $query_feriado = "SELECT COUNT(*) FROM dias_feriados WHERE fecha = ?";
    $stmt_feriado = $conn->prepare($query_feriado);
    $stmt_feriado->bind_param("s", $fecha_hora_extra);
    $stmt_feriado->execute();
    $stmt_feriado->bind_result($es_feriado);
    $stmt_feriado->fetch();
    $stmt_feriado->close();

    // Verificar si es domingo
    $es_domingo = ($dia_semana == 7);

    // Obtener horas de entrada y salida del usuario
    $query_horas = "SELECT hora_entrada, hora_salida FROM planilla WHERE id_usuario = ?";
    $stmt_horas = $conn->prepare($query_horas);
    $stmt_horas->bind_param("i", $id_usuario);
    $stmt_horas->execute();
    $stmt_horas->bind_result($hora_entrada, $hora_salida);
    $stmt_horas->fetch();
    $stmt_horas->close();

    // Verificar si se encontraron las horas de entrada y salida
    if (!$hora_entrada || !$hora_salida) {
        die("No se encontraron las horas de entrada y salida para este usuario.");
    }

    // Convertir horas a timestamps
    $timestamp_entrada = strtotime("$fecha_hora_extra $hora_entrada");
    $timestamp_salida = strtotime("$fecha_hora_extra $hora_salida");
    $timestamp_actual = time();

    // Ajustar para turnos nocturnos
    if ($hora_salida < $hora_entrada) {
        $timestamp_salida = strtotime("+1 day", $timestamp_salida);
    }

    // Calcular horas extras solo si se ha pasado la hora de salida

    $horas_extra = 0;
    if ($timestamp_actual > $timestamp_salida) {
        $horas_extra = round(($timestamp_actual - $timestamp_salida) / 3600.0, 2); // Asegurar precisión decimal
    }
    // Convertir horas extras a horas y minutos
    $horas_int = floor($horas_extra);
    $minutos_int = round(($horas_extra - $horas_int) * 60);

    // Si los minutos son 60, corregir a 0 minutos y aumentar 1 hora
    if ($minutos_int == 60) {
        $minutos_int = 0;
        $horas_int += 1;
    }

    // Obtener salario base
    $query_salario_base = "SELECT salario_base FROM planilla WHERE id_usuario = ?";
    $stmt_salario_base = $conn->prepare($query_salario_base);
    $stmt_salario_base->bind_param("i", $id_usuario);
    $stmt_salario_base->execute();
    $stmt_salario_base->bind_result($salario_base);
    $stmt_salario_base->fetch();
    $stmt_salario_base->close();

    // Verificar si se encontró el salario base
    if (!$salario_base) {
        die("Error: No se encontró el salario base para este usuario.");
    }

    // Tarifa base por hora
    $salario_quincenal = round($salario_base / 2, 2); // salario quincenal

    $tarifa_hora = round($salario_quincenal / 8, 2); 
    // Se divide entre 8 para obtener el valor por hora

    // Determinar factor de pago por horas extras
    if ($es_feriado && $es_domingo) {
        $factor_pago = 4.0; // (2x por feriado * 2x por domingo)
    } elseif ($es_feriado) {
        $factor_pago = 2.0; // Feriado entre semana
    } elseif ($es_domingo) {
        $factor_pago = 2.0; // Domingos 2x
    } else {
        $factor_pago = 1.5; // Días regulares
    }


    // Calcular monto de horas extras
    $monto_hora_extra = round($horas_extra * $tarifa_hora * $factor_pago, 2); // Redondeo final a 2 decimales


    // Registrar las horas extras en la base de datos
    $usuario_creacion = $_SESSION['usuario'] ?? 'Sistema';
    $query_insert = "INSERT INTO horas_extra (id_usuario, fecha, horas, monto_pago, fechacreacion, usuariocreacion) 
                     VALUES (?, ?, ?, ?, NOW(), ?)";
    $stmt_insert = $conn->prepare($query_insert);
    $stmt_insert->bind_param("isids", $id_usuario, $fecha_hora_extra, $horas_extra, $monto_hora_extra, $usuario_creacion);
    $stmt_insert->execute();
    $stmt_insert->close();

    // Obtener salario neto actual
    $query_salario_neto = "SELECT salario_neto FROM planilla WHERE id_usuario = ?";
    $stmt_salario_neto = $conn->prepare($query_salario_neto);
    $stmt_salario_neto->bind_param("i", $id_usuario);
    $stmt_salario_neto->execute();
    $stmt_salario_neto->bind_result($salario_neto_actual);
    $stmt_salario_neto->fetch();
    $stmt_salario_neto->close();

    // Si el salario neto actual es null o 0, inicializarlo correctamente
    $salario_neto_actual = $salario_neto_actual ?? 0;

    // Solo sumar el monto de las horas extras si el salario neto ya existe
    $nuevo_salario_neto = round($salario_neto_actual + $monto_hora_extra,1); // Aquí es donde sumas las horas extras




    // Actualizar salario neto en la base de datos
    $query_update_salario = "UPDATE planilla SET salario_neto = ? WHERE id_usuario = ?";
    $stmt_update = $conn->prepare($query_update_salario);
    $stmt_update->bind_param("di", $nuevo_salario_neto, $id_usuario);
    $stmt_update->execute();
    $stmt_update->close();

    /*
        // Mostrar resultados
        echo "Detalles de Horas Extras:<br>";
        echo "Hora de Entrada: $hora_entrada<br>";
        echo "Hora de Salida: $hora_salida<br>";
        echo "Factor: $factor_pago <br>";
        echo "Horas extras calculadas: $horas_extra horas<br>";
        echo "Horas Extras Calculadas: $horas_int horas y $minutos_int minutos<br>";
        echo "Monto por Horas Extras: ¢" . number_format($monto_hora_extra , 2, '.', ',') . "<br>";
        echo "Nuevo Salario Neto: ¢" . number_format($nuevo_salario_neto, 2, '.', ',') . "<br>";
        echo "Hora actual: " . date("Y-m-d H:i:s", $timestamp_actual) . "<br>";
        echo "Es feriado: " . ($es_feriado ? 'Sí' : 'No') . "<br>";
        echo "Es domingo: " . ($es_domingo ? 'Sí' : 'No') . "<br>";
        echo "Salario neto actual: ¢" . number_format($salario_neto_actual, 2, '.', ',') . "<br>";
        echo "Nuevo salario neto: ¢" . number_format($nuevo_salario_neto, 2, '.', ',') . "<br>";
        */
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
        <section id="main-content">
            <section class="wrapper site-min-height">
                <html lang="es">

                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Registrar Horas Extras</title>

                </head>

                <div class="container" >
                    <a href="VerPlanilla.php" class="button"><i class="bi bi-arrow-return-left"></i></a>
                    <h1 class="text-center" style="margin-left: 10%;">Calcular Horas Extras</h1>

                    <!-- Formulario con un botón para calcular horas extras -->
                    <div class="text-center">
                        <form id="calcularHorasExtrasForm" method="POST">

                          
                            <select style="text-align: center; border-color: #333; font-size: 15px;" name="tipo_dia" id="tipo_dia" class="form-control">
                                <option >Seleccione una opción</option>    
                                <option value="Regular">Regular</option>
                                <option value="Domingo">Domingo</option>
                                <option value="Feriado">Feriado</option>
                            </select>

                            <button style="margin-top: 1%;" type="submit" name="calcular_horas_extra" id="calcular_horas_extra"
                                class="btn">
                                <i class="bi bi-calculator"> Calcular</i>
                            </button>
                            <a href="Usuario_Horasextra.php" class="btn btn" style="margin-top: 1%;"><i class="bi bi-search"></i></a>

                            <a href="Eliminar_horas_extra.php" class="btn btn" style="margin-top: 1%;"><i class="bi bi-trash3-fill">Eliminar</i></a>
                        </form>
                    </div>

                    
                    

                    <div>
                        <?php if (isset($horas_extra) && isset($monto_hora_extra)): ?>
                            <div class="alert alert-success mt-3 text-center mx-auto">
                                <h4>Detalles de Horas Extras</h4>
                                <p><strong>Hora de Entrada:</strong> <?php echo $hora_entrada; ?></p>
                                <p><strong>Hora de Salida:</strong> <?php echo $hora_salida; ?></p>

                                <?php if (isset($es_feriado) && $es_feriado): ?>
                                    <p><strong>¡Hoy es un día feriado!</strong></p>
                                <?php else: ?>
                                    <p><strong>Pago de horas regulares.</strong></p>
                                <?php endif; ?>

                                <p><strong>Horas Extras Calculadas:</strong> <?php echo floor($horas_extra); ?> horas y
                                    <?php echo round(($horas_extra - floor($horas_extra)) * 60); ?> minutos
                                </p>
                                <p><strong>Monto Pago por Horas Extras:</strong>
                                    ¢<?php echo number_format($monto_hora_extra, 2); ?></p>
                                <p><strong>Horas Extras registradas exitosamente</strong></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <?php
                        // Mostrar el mensaje de error si la URL contiene el parámetro hora_registrada=error
                        if (isset($_GET['hora_registrada']) && $_GET['hora_registrada'] == 'error'): ?>
                            <div class="alert alert-danger mt-3 text-center mx-auto">
                                <strong> Ya se han registrado horas extras para este día</strong>.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    
                </div>

                
                
            </section>


</body>

</html>

<style>
    body {
        font-family: 'Ruda', sans-serif;
        background-color: #f7f7f7;
        margin: 0;
        padding: 0;
    }

    .container {
        width: 80%;
        margin: 200px auto;
        padding: 20px;
        background-color: #ffffff;
        border-radius: 8px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.48);


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


<?php
// Cerrar la conexión
$conn->close();
?>