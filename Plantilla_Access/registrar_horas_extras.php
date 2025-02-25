<?php
// Establecer la zona horaria a Costa Rica
// Esto asegura que todas los calculos con fechas y horas se realicen según la hora local de Costa Rica
date_default_timezone_set('America/Costa_Rica');

// Conexión a la base de datos
require 'conexion.php';
session_start();



// Obtener el ID del usuario desde la sesión
// Se obtiene el ID del usuario de la sesión, si no existe se asigna null
// Verificar si se presionó el botón para calcular horas extras
if (isset($_POST['calcular_horas_extra'])) {
    // Obtener el ID del usuario desde la sesión
    $id_usuario = $_SESSION['id_usuario'] ?? null;

    // Validar si hay un usuario en sesión
    if (!$id_usuario) {
        die("Error: Usuario no autenticado.");
    }

    // Obtener la fecha actual
    $fecha_hora_extra = date("Y-m-d");


    // Verificar si ya se han registrado horas extras para este usuario en la misma fecha
    $query_verificar_existencia = "SELECT COUNT(*) FROM horas_extra WHERE id_usuario = ? AND fecha = ?";
    $stmt_verificar_existencia = $conn->prepare($query_verificar_existencia);
    $stmt_verificar_existencia->bind_param("is", $id_usuario, $fecha_hora_extra);
    $stmt_verificar_existencia->execute();
    $stmt_verificar_existencia->bind_result($existe_registro);
    $stmt_verificar_existencia->fetch();
    $stmt_verificar_existencia->close();

    // Si ya existe un registro de horas extras para el mismo día, no permitir el registro
    if ($existe_registro > 0) {
        // Redirigir con el mensaje de error
        header("Location: registrar_horas_extras.php?hora_registrada=error");
        exit;  // Asegúrate de detener el script después de la redirección
    }

    // Obtener horas de entrada y salida del usuario
    $query_horas = "SELECT hora_entrada, hora_salida FROM planilla WHERE id_usuario = ?";
    $stmt_horas = $conn->prepare($query_horas);
    $stmt_horas->bind_param("i", $id_usuario);
    $stmt_horas->execute();
    $stmt_horas->bind_result($hora_entrada, $hora_salida);
    $stmt_horas->fetch();
    $stmt_horas->close();

    // Validar si hay datos de horas
    if (!$hora_entrada || !$hora_salida) {
        die("No se encontraron las horas de entrada y salida para este usuario.");
    }

    // Convertir las horas a timestamp
    $timestamp_entrada = strtotime("$fecha_hora_extra $hora_entrada");
    $timestamp_salida = strtotime("$fecha_hora_extra $hora_salida");

    // Obtener la hora actual del servidor
    $hora_actual = date("H:i:s");
    $timestamp_actual = strtotime("$fecha_hora_extra $hora_actual");

    // Manejo de turnos nocturnos (salida después de medianoche)
    if ($hora_salida < $hora_entrada) {
        $timestamp_salida = strtotime("+1 day", $timestamp_salida);
    }

    // Calcular horas extras
    if ($timestamp_actual > $timestamp_salida) {
        $diferencia_segundos = $timestamp_actual - $timestamp_salida;
        $horas_extra = $diferencia_segundos / 3600;
    } else {
        $horas_extra = 0; // Si aún no ha pasado la hora de salida, no hay horas extras
    }

    // Obtener el salario base del usuario
    $query_salario_base = "SELECT salario_base FROM planilla WHERE id_usuario = ?";
    $stmt_salario_base = $conn->prepare($query_salario_base);
    $stmt_salario_base->bind_param("i", $id_usuario);
    $stmt_salario_base->execute();
    $stmt_salario_base->bind_result($salario_base);
    $stmt_salario_base->fetch();
    $stmt_salario_base->close();

    $nuevo_salario_neto= $salario_base;

    // Validar que el salario base no sea nulo
    if (!$salario_base) {
        die("Error: No se encontró el salario base para este usuario.");
    }

    // Calcular monto de horas extras (1.5x el salario por hora)
    $tarifa_hora_extra = $salario_base / 208;  // Jornada mensual estándar en Costa Rica
    $monto_hora_extra = $horas_extra * $tarifa_hora_extra * 1.5;

    // Definir el usuario que realiza la inserción (debe venir de la sesión o sistema)
    $usuario_creacion = $_SESSION['usuario'] ?? 'Sistema';

    // Insertar las horas extras en la base de datos
    $query_insert_horas_extra = "INSERT INTO horas_extra (id_usuario, fecha, horas, monto_pago, fechacreacion, usuariocreacion) 
                                 VALUES (?, ?, ?, ?, NOW(), ?)";
    $stmt_insert_horas_extra = $conn->prepare($query_insert_horas_extra);
    $stmt_insert_horas_extra->bind_param("isids", $id_usuario, $fecha_hora_extra, $horas_extra, $monto_hora_extra, $usuario_creacion);
    $stmt_insert_horas_extra->execute();
    $stmt_insert_horas_extra->close();

    // Redondear el monto de las horas extras a dos decimales
    $monto_hora_extra = round($monto_hora_extra, 2);

    // Verificar el valor del monto de horas extras antes de actualizar
    echo "Monto de horas extras (redondeado): " . $monto_hora_extra . "<br>";

    // Verificar que el ID de usuario es válido
    echo "ID de usuario: " . $id_usuario . "<br>";
    // Obtener el salario neto actual del usuario
    $query_salario_neto = "SELECT salario_neto FROM planilla WHERE id_usuario = ?";
    $stmt_salario_neto = $conn->prepare($query_salario_neto);
    $stmt_salario_neto->bind_param("i", $id_usuario);
    $stmt_salario_neto->execute();
    $stmt_salario_neto->bind_result($salario_neto_actual);
    $stmt_salario_neto->fetch();
    $stmt_salario_neto->close();

    // Si el salario neto es nulo, se asigna un valor de 0
    if ($salario_neto_actual === null) {
        $salario_neto_actual = 0;
    }

    // Sumar el monto de horas extras al salario neto actual
    
    $nuevo_salario_neto = $salario_neto_actual + $monto_hora_extra;

    // Actualizar el salario neto con el nuevo valor calculado
    $query_actualizar_salario_neto = "UPDATE planilla SET salario_neto = ? WHERE id_usuario = ?";
    $stmt_actualizar_salario_neto = $conn->prepare($query_actualizar_salario_neto);
    $stmt_actualizar_salario_neto->bind_param("di", $nuevo_salario_neto, $id_usuario);
    $stmt_actualizar_salario_neto->execute();

    // Verificar si la actualización fue exitosa
    if ($stmt_actualizar_salario_neto->affected_rows > 0) {
        echo "Salario neto actualizado exitosamente.";
    } else {
        echo "Error al actualizar el salario neto: " . $stmt_actualizar_salario_neto->error;
    }

    $stmt_actualizar_salario_neto->close();
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

                <body>
                    <div class="container">
                        <a href="VerPlanilla.php" class="button"><i class="bi bi-arrow-return-left"></i>
                        </a>
                        <h1 class="text-center">Calcular Horas Extras</h1>

                        <!-- Formulario con un botón para calcular horas extras -->
                        <div class="text-center">

                            <form id="calcularHorasExtrasForm" method="POST">



                                <button type="submit" name="calcular_horas_extra" id="calcular_horas_extra"
                                    class="btn btn-primary">Calcular Horas Extras</button>

                                <a href="Eliminar_horas_extra.php" class="btn btn">Eliminar Horas Extras</a>

                            </form>
                        </div>

                        <div>
                            <?php if (isset($horas_extra) && isset($monto_hora_extra)): ?>
                                <div class="alert alert-success mt-3 text-center mx-auto">
                                    <h4>Detalles de Horas Extras</h4>

                                    <p><strong>Hora de Entrada:</strong> <?php echo $hora_entrada; ?></p>
                                    <p><strong>Hora de Salida:</strong> <?php echo $hora_salida; ?></p>
                                    <p><strong>Hora Actual (Servidor):</strong> <?php echo $hora_actual; ?></p>
                                    <p><strong>Horas Extras Calculadas:</strong> <?php echo floor($horas_extra); ?> horas y
                                        <?php echo round(($horas_extra - floor($horas_extra)) * 60); ?> minutos
                                    </p>
                                    <p><strong>Monto por Horas Extras:</strong>
                                        ¢<?php echo number_format($monto_hora_extra, 2); ?></p>
                                </div>
                            <?php endif; ?>

                        </div>

                        <div>
                            <?php
                            // Mostrar el mensaje de error si la URL contiene el parámetro hora_registrada=error
                            if (isset($_GET['hora_registrada']) && $_GET['hora_registrada'] == 'error'): ?>
                                <div class="alert alert-success mt-3 text-center mx-auto">
                                    ¡Ya se han registrado horas extras para este día!
                                </div>
                            <?php endif; ?>
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