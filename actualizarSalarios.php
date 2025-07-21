<?php
ob_start();  // Inicia el búfer de salida
session_start();
require 'conexion.php';
require 'template.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
$conn = obtenerConexion();



//validación para que solo el administrador master acceda
// if (!isset($_SESSION['id_rol']) || (int)$_SESSION['id_rol'] !== 3) {
//     header("Location: index.php");
//     exit;
// }

$mensaje = "";

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

    <title>Actualizar Salarios</title>


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

    <!--main content start-->
    <section id="main-content">
        <section class="wrapper site-min-height">


            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $id_usuario = $_POST["id_usuario"];
                $nuevo_salario_base = $_POST["nuevo_salario_base"];

                // Función para calcular las retenciones quincenales
                function calcularRetencionesQuincenales($salario_base)
                {
                    $salario_base = (float) $salario_base;

                    // Calcular Seguro Social (10.5% del salario)
                    $seguro_social = $salario_base * 0.105;

                    // Calcular Impuesto sobre la Renta
                    if ($salario_base <= 941000) {
                        $impuesto_renta = 0;
                    } elseif ($salario_base <= 1385000) {
                        $impuesto_renta = ($salario_base - 941000) * 0.10;
                    } else {
                        $impuesto_renta = ((1385000 - 941000) * 0.10) + (($salario_base - 1385000) * 0.15);
                    }

                    // Convertir retenciones a valores quincenales
                    $seguro_social_quincenal = $seguro_social / 2;
                    $impuesto_renta_quincenal = $impuesto_renta / 2;
                    $total_retenciones_quincenal = $seguro_social_quincenal + $impuesto_renta_quincenal;

                    return [
                        'seguro_social' => $seguro_social_quincenal,
                        'impuesto_renta' => $impuesto_renta_quincenal,
                        'total_retenciones' => $total_retenciones_quincenal
                    ];
                }

                // Verificar si el usuario existe en Planilla
                $query_select = "SELECT salario_base FROM Planilla WHERE id_usuario = ?";
                $stmt_select = $conn->prepare($query_select);
                $stmt_select->bind_param("i", $id_usuario);
                $stmt_select->execute();
                $stmt_select->store_result();

                if ($stmt_select->num_rows > 0) {
                    $stmt_select->bind_result($salario_anterior);
                    $stmt_select->fetch();
                    $stmt_select->close();

                    // Calcular el ajuste salarial (diferencia entre el nuevo y el anterior)
                    $ajuste_salarial = $nuevo_salario_base - $salario_anterior;

                    // Calcular retenciones quincenales con el nuevo salario
                    $retenciones = calcularRetencionesQuincenales($nuevo_salario_base);
                    $seguro_social_quincenal = $retenciones['seguro_social'];
                    $total_retenciones_quincenal = $retenciones['total_retenciones'];

                    // Calcular el nuevo salario neto después de retenciones
                    $nuevo_salario_neto = ($nuevo_salario_base / 2) - $total_retenciones_quincenal;

                    // Actualizar Planilla con los nuevos valores
                    $query_update = "UPDATE Planilla SET salario_base = ?, retenciones = ?, salario_neto = ? WHERE id_usuario = ?";
                    $stmt_update = $conn->prepare($query_update);
                    $stmt_update->bind_param("dddi", $nuevo_salario_base, $total_retenciones_quincenal, $nuevo_salario_neto, $id_usuario);

                    if ($stmt_update->execute()) {
                        // Insertar el cambio en Historial_Salarios
                        $fecha_cambio = date("Y-m-d");
                        $usuariocreacion = "admin"; // Cambia según la sesión de usuario
            
                        $query_historial = "INSERT INTO Historial_Salarios (id_usuario, nuevo_salario_base, ajuste, nuevo_salario_neto, fecha_cambio, usuariocreacion) 
                                                VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt_historial = $conn->prepare($query_historial);
                        $stmt_historial->bind_param("idddss", $id_usuario, $nuevo_salario_base, $ajuste_salarial, $nuevo_salario_neto, $fecha_cambio, $usuariocreacion);

                        if ($stmt_historial->execute()) {
                            $mensaje = "✅ Salario actualizado correctamente.<br>
                                            Nuevo Salario Base: ₡" . number_format($nuevo_salario_base, 2) . "<br>
                                            Ajuste Salarial: ₡" . number_format($ajuste_salarial, 2) . "<br>
                                            Nuevo Salario Neto Quincenal: ₡" . number_format($nuevo_salario_neto, 2);
                        } else {
                            $mensaje = "Error al guardar en Historial_Salarios: " . $stmt_historial->error;
                        }
                        $stmt_historial->close();

                        // Actualizar la tabla de deducciones
                        $query_select_deduccion = "SELECT id_deduccion FROM deducciones WHERE id_usuario = ?";
                        $stmt_select_deduccion = $conn->prepare($query_select_deduccion);
                        $stmt_select_deduccion->bind_param("i", $id_usuario);
                        $stmt_select_deduccion->execute();
                        $stmt_select_deduccion->store_result();

                        if ($stmt_select_deduccion->num_rows > 0) {
                            $stmt_select_deduccion->bind_result($id_deduccion);
                            $stmt_select_deduccion->fetch();
                            $stmt_select_deduccion->close();

                            // Calcular el monto mensual de seguro social
                            $seguro_social_mensual = $seguro_social_quincenal * 2;

                            // Actualizar deducción con los nuevos valores
                            $query_update_deduccion = "UPDATE deducciones 
                                                            SET monto_quincenal = ?, monto_mensual = ?, saldo_pendiente = ?, deuda_total = ? 
                                                            WHERE id_deduccion = ?";
                            $stmt_update_deduccion = $conn->prepare($query_update_deduccion);
                            $stmt_update_deduccion->bind_param("ddddd", $seguro_social_quincenal, $seguro_social_mensual, $seguro_social_quincenal, $seguro_social_quincenal, $id_deduccion);

                            if ($stmt_update_deduccion->execute()) {
                                $mensaje .= "<br>✅ Deducción actualizada correctamente.";
                            } else {
                                $mensaje .= "<br>Error al actualizar la deducción: " . $stmt_update_deduccion->error;
                            }
                            $stmt_update_deduccion->close();
                        } else {
                            $mensaje .= "<br>No se encontró la deducción para el usuario.";
                        }
                    } else {
                        $mensaje = "Error al actualizar Planilla: " . $stmt_update->error;
                    }
                    $stmt_update->close();
                } else {
                    $mensaje = "El usuario no está registrado en Planilla.";
                }
            }

            // Obtener la lista de empleados desde Planilla
            $query_empleados = "SELECT u.id_usuario, u.nombre, p.salario_base FROM Usuario u 
                                    JOIN Planilla p ON u.id_usuario = p.id_usuario";
            $result_empleados = $conn->query($query_empleados);

            $conn->close();

            ?>
            <title>Actualizar Salarios</title>
            <link href="assets/css/bootstrap.css" rel="stylesheet">
            <link href="assets/css/style.css" rel="stylesheet">

            <style>
                .container-fluid {
                    max-width: 800px;
                    /* Increase the width of the container */
                    max-height: 800px;
                    margin: 50px auto;
                    /* Center the container */
                    padding: 40px;
                    /* Add more space inside the container */
                    background-color: white;
                    /* Background color */
                    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                    /* Box shadow */
                    border-radius: 10px;
                    /* Rounded corners */
                    color: black;
                }



                h2 {
                    font-size: 24px;
                    font-weight: bold;
                    margin-bottom: 20px;
                }

                .form-group {
                    margin-bottom: 20px;
                    /* Spacing between fields */
                    max-height: 1000px;
                }

                .form-group.text-center {
                    margin-top: 20px;
                    /* More space between fields and buttons */
                }

                .form-group button,
                .form-group a {

                    padding: 10px 20px;
                    font-size: 15px;
                }
            </style>

            <body>
                <section id="container">
                    <!-- Añadimos un contenedor blanco aquí -->
                    <div class="container-fluid">
                        <div class="card" style="border-radius: 10px;">
                            <div class="card-body">
                                <h2 class="text-center mt-4">Actualizar Salarios</h2>
                                <form action="" method="POST" class="mt-4">
                                    <div class="form-group">
                                        <label for="id_usuario">Empleado:</label>
                                        <select id="id_usuario" name="id_usuario" class="form-control" required>
                                            <option value="">Seleccione un empleado</option>
                                            <?php while ($row = $result_empleados->fetch_assoc()): ?>
                                                <option value="<?php echo $row['id_usuario']; ?>">
                                                    <?php echo $row['nombre']; ?> - Salario:
                                                    ₡<?php echo number_format($row['salario_base'], 2); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="nuevo_salario_base">Nuevo Salario Base:</label>
                                        <input type="number" step="0.01" id="nuevo_salario_base"
                                            name="nuevo_salario_base" class="form-control" required>
                                    </div>
                                    <div class="form-group d-flex justify-content-center gap-3 flex-wrap mt-4">
                                        <button type="submit" class="btn btn-primary"
                                            style="background-color: #147964; border-color: #147964; color: white;">
                                            Actualizar Salario
                                        </button>

                                        <a href="VerPlanilla.php" class="btn"
                                            style="background-color: #0B4F6C; border-color: #0B4F6C; color: white;">
                                            Volver
                                        </a>
                                    </div>

                                </form>
                                <?php if (!empty($mensaje)): ?>
                                    <div class="alert alert-info mt-3"><?php echo $mensaje; ?></div>
                                <?php endif; ?>
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

<?php
ob_end_flush();
?>