<?php
require 'conexion.php';
session_start();

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_POST["id_usuario"];
    $razon = $_POST["razon"];
    $monto_total = $_POST["monto_total"];
    $fecha_aplicacion = date("Y-m-d");
    $usuariocreacion = "admin"; // El nombre del usuario que crea el bono
    $fechacreacion = date("Y-m-d");

    // Insertar en la tabla Bonos
    $query_bono = "INSERT INTO Bonos (id_usuario, razon, monto_total, fecha_aplicacion, fechacreacion, usuariocreacion)
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_bono = $conn->prepare($query_bono);
    $stmt_bono->bind_param("isssss", $id_usuario, $razon, $monto_total, $fecha_aplicacion, $fechacreacion, $usuariocreacion);

    if ($stmt_bono->execute()) {
        // Obtener el ID del bono recién insertado
        $id_bono = $stmt_bono->insert_id;

        // Actualizar el salario base en la tabla Planilla
        $query_salario = "UPDATE Planilla 
        SET total_bonos = ? 
        WHERE id_usuario = ?";
        $stmt_salario = $conn->prepare($query_salario);
        $stmt_salario->bind_param("di", $monto_total, $id_usuario);

        if ($stmt_salario->execute()) {
            $mensaje = "Bono registrado correctamente";
        } else {
            $mensaje = "Error al actualizar el salario base.";
        }

        $stmt_salario->close();
    } else {
        $mensaje = "Error al registrar el bono.";
    }

    $stmt_bono->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="es">

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
</head>

<body>
    <div class="container">
        <h2 class="text-center mt-4">Aplicar Bono Salarial</h2>
        <form action="" method="POST" class="mt-4">
            <div class="form-group">
                <label for="id_usuario">ID del Usuario:</label>
                <input type="number" name="id_usuario" class="form-control" required>
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
                <button type="submit" class="btn btn-success">Aplicar Bono</button>
                <a href="MostrarUsuarios.php" class="btn btn-secondary">Volver</a>
            </div>
        </form>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-info mt-3"><?php echo $mensaje; ?></div>
        <?php endif; ?>
    </div>

    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
</body>

</html>
