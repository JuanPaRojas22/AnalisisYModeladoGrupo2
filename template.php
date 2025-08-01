<?php

// Verifica si el usuario está autenticado
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == true) {
    $nombre = $_SESSION['nombre'];  // Obtener el nombre del usuario
    $username = $_SESSION['username'];
    $id_rol = $_SESSION['id_rol'];     // Obtener el ID del rol
    $direccion = isset($_SESSION['direccion_imagen']) ? $_SESSION['direccion_imagen'] : 'assets/img/default-profile.png'; // Imagen de perfil

}


// Parámetros de conexión
$host = "accespersoneldb.mysql.database.azure.com";
$user = "adminUser";
$password = "admin123+";
$dbname = "gestionEmpleados";
$port = 3306;

// Ruta al certificado CA para validar SSL
$ssl_ca = '/home/site/wwwroot/certs/BaltimoreCyberTrustRoot.crt.pem';

// Inicializamos mysqli
$conn = mysqli_init();

// Configuramos SSL
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);


// Intentamos conectar usando SSL (con la bandera MYSQLI_CLIENT_SSL)
if (!$conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Establecemos el charset
mysqli_set_charset($conn, "utf8mb4");

//echo "Conectado correctamente con SSL.";

// Consulta para obtener el número de nuevos aportes
$query = "SELECT COUNT(*) AS aporte FROM aportes WHERE aporte = 0";  // Ajusta la tabla y condición según tu estructura
$result = $conn->query($query);

// Obtener el número de nuevos aportes
$row = $result->fetch_assoc();
$aporte = $row['aporte'];

// Cerrar la conexión


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



    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!--external css-->
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="assets/css/zabuto_calendar.css">
    <link rel="stylesheet" type="text/css" href="assets/js/gritter/css/jquery.gritter.css" />
    <link rel="stylesheet" type="text/css" href="assets/lineicons/style.css">

    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/style-responsive.css" rel="stylesheet">

    <script src="assets/js/chart-master/Chart.js"></script>

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    
    <!--<style>
         td, div {
        color: black !important;
    }
    -->
</style>


    </style>
</head>

<body>
    <section id="container">
        <header class="header black-bg">
            <div class="sidebar-toggle-box">
                <div class="fa fa-bars tooltips" data-placement="right" data-original-title="Toggle Navigation"></div>
            </div>
            <a href="index.php" class="logo"><b>Access Perssonel</b></a>
            <div class="nav notify-row" id="top_menu">
                <!-- Notifications -->
                <ul class="nav top-menu">
                    <!-- Tasks Dropdown -->
                    <!--<li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <i class="fa fa-tasks"></i>
                            <span class="badge bg-theme">4</span>
                        </a>
                        <ul class="dropdown-menu extended tasks-bar">
                            <div class="notify-arrow notify-arrow-green"></div>
                            <li>
                                <p class="green">You have 4 pending tasks</p>
                            </li>
                          
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
                            
                        </ul>
                    </li>-->
                    <!-- Messages Dropdown -->
                    <!-- Messages Dropdown -->
                    <li id="header_inbox_bar">
                        <!-- El enlace 'href' redirige a la página aportes.php -->
                        <a href="aportes.php">
                            <i class="fa fa-envelope-o"></i>
                            <!-- Aquí mostramos el número de nuevos aportes -->
                            <span class="badge bg-theme" id="message-count">
                                <?php echo $aporte > 0 ? $aporte : ''; ?>
                            </span>
                        </a>
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
                            <li><a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
                        </ul>
                    </li>

                </ul>



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
                            <li class="mt">

                                <h5 class="centered">Bienvenido, <?php echo $_SESSION['nombre']; ?>!</h5>
                            </li>

                           <!-- <li class="mt">
                                <a class="active" href="index.php">
                                    <i class="fa fa-dashboard"></i>
                                    <span>Dashboard</span>
                                </a>
                            </li>-->


                            

                            <!--
                            <li class="sub-menu">
                                <a href="javascript:;">
                                    <i class="fa fa-cogs"></i>
                                    <span>Components</span>
                                </a>
                                <ul class="sub">
                                    <li><a href="calendar.html">Calendar</a></li>
                                    <li><a href="gallery.html">Gallery</a></li>                           
                                </ul>
                            </li>-->
                            
                            <li class="sub-menu">
                            <?php if (in_array($id_rol, [1, 2])): ?>
                                <a href="javascript:;">
                                    <i class="fa fa-desktop"></i>
                                    <span>Reportes</span>
                                </a>
                                <ul class="sub">
                                    <li><a href="reporte_ins.php"><i class="bi bi-person-badge-fill"></i>
                                            INS</a></li>
                                    <li><a href="reporte_ccss.php"><i class="bi bi-heart-fill"></i>CCSS</a></li>
                                    <li><a href="reporte_bac.php"><i class="bi bi-credit-card"></i>
                                            BAC</a></li>
                                    <li><a href="ver_reporte.php"><i class="bi bi-brightness-low-fill"></i>
                                            Vacaciones</a></li>
                                    <li><a href="reporte_hacienda.php"><i class="bi bi-bank"></i>Hacienda</a></li>
                                    <li><a href="Dias_Feriados.php"><i class="bi bi-calendar3"></i>
                                    Feriados</a></li>
                                    <li><a href="historial_salarios.php"><i class="bi bi-calendar3"></i>
                                    Historial Salarios</a></li>
                                </ul>
                                <?php endif; ?>
                            </li>
                            <?php if (in_array($id_rol, [1, 2])): ?>

                                <li class="sub-menu">
                                    <a href="javascript:;">
                                        <i class="bi bi-person-fill-gear"></i>
                                        <span>Administracion</span>
                                    </a>

                                    <ul class="sub">
                                        <li><a href="VerPlanilla.php"><i
                                                    class="bi bi-journal-bookmark"></i><span>Planilla</span></a></li>
                                        <li><a href="admin_beneficios.php"><i class="bi bi-gift"></i>Beneficios</a></li>

                                        <?php if ($_SESSION['id_rol'] == 2): ?>
                                            <li><a href="MostrarUsuarios.php"><i
                                                        class="bi bi-person-lines-fill"></i><span>Usuarios</span></a></li>
                                        <?php endif; ?>

                                        <li class="nav-item">
                                            <a href="vacaciones.php" class="nav-link">
                                                <i class="bi bi-gear"></i> Vacaciones
                                            </a>
                                    
                                        </li>
                                <li><a href="registrarAusencia.php"><i class="bi bi-person-dash"></i>Registrar Ausencia</a>
                                </li>
                                <li><a href="reporteAusencias.php"><i class="bi bi-bar-chart"></i>Reporte de Ausencias</a>
                                </li>
                                <li><a href="reporteAntiguedad.php"><i class="bi bi-clock-history"></i>Reporte de
                                        Antigüedad</a></li>
                                <li><a href="registrarBeneficiosAntiguedad.php"><i class="bi bi-gift"></i>Registrar Antigüedad</a></li>
                            <?php endif; ?>

                        </ul>
                        <li><a href="beneficios.php"><i class="bi bi-sun"></i><span>Beneficios</span></a></li>
                        <li>
                            <a href="SolicitarVacacion.php">
                                <i class="bi bi-sun"></i>
                                <span>Vacaciones</span>
                            </a>
                        </li>
                        <li><a href="preguntasfreq.php"><i class="bi bi-question-octagon-fill"></i>Preguntas Frecuentes</a></li>

                </ul>
                </li>

                <!-- <li><a href="preguntasfreq.php"><i class="bi bi-question-octagon-fill"></i>Preguntas Frecuentes</a></li>-->

                </li>


                </ul>
                <!-- sidebar menu end-->
            </div>
        </aside>

        <!-- Botón flotante 
        <button class="boton-flotante" onclick="abrirModal()">✨ Hacer un aporte</button>
        -->

        <!-- Modal -->
       <!--  <div id="miModal" class="modal">
            <div class="modal-contenido">
                <span class="cerrar" onclick="cerrarModal()">&times;</span>
                <h2>Haz tu aporte</h2>
                <form id="enviarAporte">
                    <input type="text" value=" echo $_SESSION['nombre']; ?>" readonly>
                    <textarea  id="aporte" name="aporte" placeholder="Escribe tu aporte..." required></textarea>
                    <button type="submit" class="enviar">Enviar</button>
                </form>
            </div>
        </div>-->
                    
        <!-- Footer -->
        <footer class="site-footer">
            <div class="text-center">2025 - Acces Perssonel</div>
        </footer>
    </section>


    <script>
        document.addEventListener("DOMContentLoaded", function () {
            console.log("DOM cargado");

            // Función para abrir el modal
            function abrirModal() {
                console.log("Modal abierto");
                document.getElementById("miModal").style.display = "block";
            }

            // Función para cerrar el modal
            function cerrarModal() {
                console.log("Modal cerrado");
                document.getElementById("miModal").style.display = "none";
            }

           // Función para enviar el aporte
            function enviarAporte(event) {
                event.preventDefault();
                const mensaje = document.getElementById("aporte").value;
                console.log("Enviando aporte:", mensaje);

                const formData = new FormData();
                formData.append("aporte", mensaje);

                fetch("guardar_aporte.php", {
                    method: "POST",
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        console.log(data); // para ver qué responde PHP
                        if (data.success) {
                            alert("¡Aporte enviado con éxito!");
                            document.getElementById("aporte").value = "";
                            cerrarModal(); // Cierra el modal tras enviar
                        } else {
                            alert("Error: " + data.message);
                        }
                    })
                    .catch(err => {
                        alert("Error al enviar el aporte");
                        console.error(err);
                    });
            }

            // Verificar si los elementos existen antes de agregar los eventos
            const botonFlotante = document.querySelector(".boton-flotante");
            const cerrarBoton = document.querySelector(".cerrar");
            const formulario = document.getElementById("enviarAporte");

            if (botonFlotante) {
                botonFlotante.addEventListener("click", abrirModal);
            } else {
                console.error("No se encontró el botón flotante.");
            }

            if (cerrarBoton) {
                cerrarBoton.addEventListener("click", cerrarModal);
            } else {
                console.error("No se encontró el botón de cerrar.");
            }

            if (formulario) {
                formulario.addEventListener("submit", enviarAporte);
            } else {
                console.error("No se encontró el formulario.");
            }
        });

    </script>

</body>






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

<!--<script type="text/javascript">
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
</script>-->

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

<style>
    .profile-container {
        margin-left: 250px;
        padding: 60px;
    }
</style>

</html>
