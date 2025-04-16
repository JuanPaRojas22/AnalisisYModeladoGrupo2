<?php
require 'conexion.php';
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  header("Location: login.php");
  exit;
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

  <title>Aplicar Deducciones</title>


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

              <p class="centered"><a href="profile.html"><img src="assets/img/ui-sam.jpg" class="img-circle"
                    width="60"></a></p>
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
                  <li><a href="VerPlanilla.php"><i class="bi bi-journal-bookmark"></i><span>Planilla</span></a></li>
                  <li><a href="MostrarUsuarios.php"><i class="bi bi-person-lines-fill"></i><span>Usuarios</span></a>
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
    <section id="main-content">
      <section class="wrapper site-min-height">

        <?php
        // Consulta para obtener las planillas existentes con información del usuario
        $query_planilla = "SELECT p.id_planilla, p.id_usuario, u.nombre, u.apellido, p.salario_base 
FROM Planilla p 
JOIN Usuario u ON p.id_usuario = u.id_usuario";
        $result_planilla = $conn->query($query_planilla);

        // Función para calcular retenciones mensuales
        function calcularRetenciones($salario_base)
        {
          // Convertir el salario a float (por si se recibe como string)
          $salario_base = (float) $salario_base;
          $seguro_social = $salario_base * 0.105;
          if ($salario_base <= 941000) {
            $impuesto_renta = 0;
          } elseif ($salario_base <= 1385000) {
            $impuesto_renta = ($salario_base - 941000) * 0.10;
          } else {
            $impuesto_renta = ((1385000 - 941000) * 0.10) + (($salario_base - 1385000) * 0.15);
          }
          $total_retenciones = $seguro_social + $impuesto_renta;
          return [
            'salario_base' => $salario_base,
            'seguro_social' => $seguro_social,
            'impuesto_renta' => $impuesto_renta,
            'total_retenciones' => $total_retenciones
          ];
        }

        $mensaje = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
          // Datos recibidos del formulario (el salario_base es el valor mensual)
          $id_planilla = $_POST["id_planilla"];
          $id_usuario = $_POST["id_usuario"];
          $salario_base = $_POST["salario_base"];

          // Calcular retenciones mensuales
          $retenciones_mensuales = calcularRetenciones($salario_base);

          // Calcular valores quincenales (dividiendo entre 2)
          $retenciones_quincenales = [
            'salario_base' => $retenciones_mensuales['salario_base'] / 2,
            'seguro_social' => $retenciones_mensuales['seguro_social'] / 2,
            'impuesto_renta' => $retenciones_mensuales['impuesto_renta'] / 2,
            'total_retenciones' => $retenciones_mensuales['total_retenciones'] / 2
          ];
          $salario_neto_quincenal = ($retenciones_mensuales['salario_base'] / 2) - $retenciones_quincenales['total_retenciones'];

          // Actualizar la planilla
          if (!empty($id_planilla)) {
            $query = "UPDATE Planilla 
SET salario_base = ?, retenciones = ?, salario_neto = ?, fechamodificacion = CURDATE() 
WHERE id_planilla = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("dddi", $retenciones_quincenales['salario_base'], $retenciones_quincenales['total_retenciones'], $salario_neto_quincenal, $id_planilla);
          } else {
            $query = "INSERT INTO Planilla (id_usuario, salario_base, retenciones, salario_neto, fechacreacion) 
VALUES (?, ?, ?, ?, CURDATE())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iddd", $id_usuario, $retenciones_quincenales['salario_base'], $retenciones_quincenales['total_retenciones'], $salario_neto_quincenal);
            $stmt->execute();
            $id_planilla = $stmt->insert_id;
          }
          $stmt->execute();

          // Insertar deducciones en la tabla deducciones
// La consulta inserta 11 valores (los otros 4 se establecen fijos con CURDATE() y 'admin')
          $query_deduccion = "INSERT INTO deducciones 
(id_usuario, razon, deudor, concepto, lugar, monto_quincenal, monto_mensual, aportes, saldo_pendiente, deuda_total, saldo_pendiente_dolares, fechacreacion, usuariocreacion, fechamodificacion, usuariomodificacion)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE(), 'admin', CURDATE(), 'admin')";
          $stmt_deduccion = $conn->prepare($query_deduccion);

          // Variables comunes para deducciones
          $deudor = "";
          $concepto = "";
          $lugar = "";

          // Para Seguro Social (quincenal)
          $razon = "Seguro Social";
          // Aporte quincenal: se toma la mitad del valor mensual calculado
          $aporte_quincenal = $retenciones_mensuales['seguro_social'] / 2;
          // Para efectos de la deducción, asumimos que el monto mensual es el doble del quincenal
          $monto_quincenal = $aporte_quincenal;
          $monto_mensual = $aporte_quincenal * 2;
          $saldo_pendiente = 0;
          $deuda_total = 0;
          $saldo_pendiente_dolares = 0;
          // La cadena de tipos es: i (int), 4 strings, y 6 doubles = "issssdddddd"
          $stmt_deduccion->bind_param("issssdddddd", $id_usuario, $razon, $deudor, $concepto, $lugar, $monto_quincenal, $monto_mensual, $aporte_quincenal, $saldo_pendiente, $deuda_total, $saldo_pendiente_dolares);
          $stmt_deduccion->execute();

          // Para Impuesto sobre la Renta (quincenal)
          $razon = "Impuesto sobre la Renta";
          $aporte_quincenal = $retenciones_mensuales['impuesto_renta'] / 2;
          $monto_quincenal = $aporte_quincenal;
          $monto_mensual = $aporte_quincenal * 2;
          $saldo_pendiente = 0;
          $deuda_total = 0;
          $saldo_pendiente_dolares = 0;
          $stmt_deduccion->bind_param("issssdddddd", $id_usuario, $razon, $deudor, $concepto, $lugar, $monto_quincenal, $monto_mensual, $aporte_quincenal, $saldo_pendiente, $deuda_total, $saldo_pendiente_dolares);
          $stmt_deduccion->execute();

          $mensaje = "Retenciones quincenales aplicadas correctamente.<br>Salario Neto quincenal actualizado: ₡" . number_format($salario_neto_quincenal, 2);

          $stmt->close();
          $stmt_deduccion->close();
          $conn->close();
        }
        ?>

        <!DOCTYPE html>
        <html lang="es">

        <head>
          <meta charset="UTF-8">
          <meta name="viewport" content="width=device-width, initial-scale=1.0">
          <title>Calcular Retenciones Quincenales</title>
          <link href="assets/css/bootstrap.css" rel="stylesheet">
          <link href="assets/css/style.css" rel="stylesheet">
        </head>

        <style>
/* Container Styles */
.container-fluid {
  min-height: 600px; 
  max-width: 1000px; /* Limit the container width */
  margin: 50px auto; /* Center the container */
  padding: 30px; /* Padding inside the container */
  background-color: white; /* White background */
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Add shadow */
  border-radius: 15px; /* Round the corners */
  height: 100%;
}

/* Card Body Styles */
.card-body {
  padding: 20px;
}

/* Heading Style */
h2 {
  font-size: 28px;
  font-weight: bold;
  margin-bottom: 20px;
}

/* Form Group Styles */
.form-group {
  margin-bottom: 20px; /* Add space between form elements */
}

/* Button Styles */
button[type="submit"],
a.btn {
  padding: 10px 20px;
  font-size: 16px;
  border-radius: 5px;
  text-decoration: none;
  display: inline-block;
  width: auto;
}

button[type="submit"] {
  background-color: #147964; /* Green */
  color: white;
  border: none;
}

button[type="submit"]:hover {
  background-color: #147964;
}

a.btn {
  background-color: #0B4F6C; /* Blue */
  color: white;
}

a.btn:hover {
  background-color: #0B4F6C;
}


        </style>
       <body>
  <section id="container">
    <div class="container-fluid">
      <div class="card" style="border-radius: 15px; padding: 30px; box-shadow: 0 4px 10px rgb(255, 255, 255);">
        <div class="card-body">
          <h2 class="text-center mb-4">Aplicar Bono Salarial</h2>
          <form action="" method="POST" class="form-horizontal">
            <!-- Select Employee -->
            <div class="form-group">
              <label for="id_usuario">Seleccione un Usuario:</label>
              <select id="id_usuario" name="id_usuario" class="form-control" required>
                <option value="">Seleccione un usuario</option>
                <!-- Add employee options dynamically here -->
              </select>
            </div>

            <!-- Salary Field -->
            <div class="form-group">
              <label for="salario_actual">Salario Actual:</label>
              <input type="text" id="salario_actual" name="salario_actual" class="form-control" readonly>
            </div>

            <!-- Reason Field -->
            <div class="form-group">
              <label for="razon_bono">Razón del Bono:</label>
              <input type="text" id="razon_bono" name="razon_bono" class="form-control" required>
            </div>

            <!-- Amount Field -->
            <div class="form-group">
              <label for="monto_bono">Monto del Bono:</label>
              <input type="number" id="monto_bono" name="monto_bono" class="form-control" required>
            </div>

            <!-- Buttons -->
            <div class="form-group text-center">
              <button type="submit" class="btn btn-success">Aplicar Bono</button>
              <a href="VerPlanilla.php" class="btn btn-info">Volver</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
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