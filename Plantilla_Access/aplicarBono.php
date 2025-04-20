<?php
require 'conexion.php';
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
require 'template.php';
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

    <title>Aplicar Bonos</title>


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
    <style>
        td, div {
            color: black !important;
        }
    </style>
</head>

<body>
    
<section id="container">
        
        <style>
        .form-container {
    background-color: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Sombra para el efecto de profundidad */
    width: 60%; /* Ajusta el ancho del contenedor */
    margin: 20px auto; /* Centra el contenedor */
}

.container {
    width: 80%; /* Ajusta el ancho del contenedor */
    margin: 0 auto; /* Centra el contenedor */
}

h2 {
    font-size: 28px;
}

.form-group label {
    font-weight: bold;
}

.form-control {
    border-radius: 5px;
    padding: 10px;
    font-size: 16px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
}

.btn {
    width: 150px;
    padding: 10px;
}

.btn-success {
    background-color: #147964;
    border-color: #147964;
}

.btn-secondary {
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn:hover {
    opacity: 0.9;
} 
</style>
        <section id="main-content">
            <section class="wrapper site-min-height">

                <?php
                $mensaje = "";

                // Obtener lista de usuarios con su salario
                $query_usuarios = "SELECT u.id_usuario, u.nombre, p.salario_base 
                                   FROM Usuario u 
                                   LEFT JOIN Planilla p ON u.id_usuario = p.id_usuario";
                $result_usuarios = $conn->query($query_usuarios);

                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    $id_usuario = $_POST["id_usuario"];
                    $razon = $_POST["razon"];
                    $monto_total = $_POST["monto_total"];
                    $fecha_aplicacion = date("Y-m-d");
                    $usuariocreacion = "admin";
                    $fechacreacion = date("Y-m-d");

                    // Verificar si el usuario tiene un salario registrado en Planilla
                    $query_verificar = "SELECT salario_base FROM Planilla WHERE id_usuario = ?";
                    $stmt_verificar = $conn->prepare($query_verificar);
                    $stmt_verificar->bind_param("i", $id_usuario);
                    $stmt_verificar->execute();
                    $result_verificar = $stmt_verificar->get_result();

                    if ($result_verificar->num_rows == 0) {
                        $mensaje = "Error: El usuario no tiene un salario registrado en Planilla.";
                    } else {
                        // Insertar el bono en la tabla Bonos
                        $query_bono = "INSERT INTO Bonos (id_usuario, razon, monto_total, fecha_aplicacion, fechacreacion, usuariocreacion)
                                       VALUES (?, ?, ?, ?, ?, ?)";
                        $stmt_bono = $conn->prepare($query_bono);
                        $stmt_bono->bind_param("isssss", $id_usuario, $razon, $monto_total, $fecha_aplicacion, $fechacreacion, $usuariocreacion);

                        if ($stmt_bono->execute()) {
                            // Actualizar el salario en la tabla Planilla (eliminamos la referencia a id_bono)
                            $query_salario = "UPDATE Planilla 
                                              SET salario_base = salario_base + ? 
                                              WHERE id_usuario = ?";
                            $stmt_salario = $conn->prepare($query_salario);

                            if (!$stmt_salario) {
                                die("Error en la consulta: " . $conn->error);
                            }

                            $stmt_salario->bind_param("di", $monto_total, $id_usuario);

                            if ($stmt_salario->execute()) {
                                $mensaje = "Bono aplicado correctamente. El salario se ha actualizado.";
                            } else {
                                $mensaje = "Error al actualizar el salario en Planilla: " . $stmt_salario->error;
                            }

                            $stmt_salario->close();
                        } else {
                            $mensaje = "Error al registrar el bono.";
                        }

                        $stmt_bono->close();
                    }

                    $stmt_verificar->close();
                    $conn->close();
                }
                ?>

                <!DOCTYPE html>
                <html lang="es">

                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Aplicar Bono Salarial</title>
                    <link href="assets/css/bootstrap.css" rel="stylesheet">
                    <link href="assets/css/style.css" rel="stylesheet">
                    <script>
                        function actualizarSalario() {
                            var select = document.getElementById("id_usuario");
                            var salario = select.options[select.selectedIndex].getAttribute("data-salario");
                            document.getElementById("salario_actual").value = salario ? salario : "No registrado";
                        }
                    </script>
                </head>

                <body>
                <div class="container mt-5 form-container">
    <h2 class="text-center mb-4">Aplicar Bono Salarial</h2>
    <form action="" method="POST">
        <div class="form-group">
            <label for="id_usuario">Seleccione un Usuario:</label>
            <select name="id_usuario" id="id_usuario" class="form-control" required onchange="actualizarSalario()">
                <option value="">Seleccione un usuario</option>
                <?php while ($row = $result_usuarios->fetch_assoc()): ?>
                    <option value="<?= $row['id_usuario']; ?>" data-salario="<?= $row['salario_base']; ?>">
                        <?= $row['nombre']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="salario_actual">Salario Actual:</label>
            <input type="text" id="salario_actual" class="form-control" disabled>
        </div>

        <div class="form-group">
            <label for="razon">Razón del Bono:</label>
            <input type="text" name="razon" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="monto_total">Monto del Bono:</label>
            <input type="number" step="0.01" name="monto_total" class="form-control" required>
        </div>

        <div class="text-center mt-4">
        <button type="submit" class="btn" style="background-color: #147964; border-color: #147964; color: white;">Aplicar Bono</button>
        <a href="VerPlanilla.php" class="btn" style="background-color: #0B4F6C; border-color: #0B4F6C; color: white;">Volver</a>
        </div>
    </form>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-info mt-3"><?= $mensaje; ?></div>
    <?php endif; ?>
</div>
                        

                        <?php if (!empty($mensaje)): ?>
                            <div class="alert alert-info mt-3"><?= $mensaje; ?></div>
                        <?php endif; ?>
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