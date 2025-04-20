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
    <style>
        td, div {
            color: black !important;
        }
    </style>
</head>

<body>

  <section id="container">
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
</body>


        </html>

        
        